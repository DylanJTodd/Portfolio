<?php
// Ensure the JSON extension is loaded
if (!extension_loaded('json')) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON extension is not loaded']);
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; //git ignored

// Get request method and parse endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

// First part of the path determines the resource (users, messages, settings, notes)
$resource = $request[0] ?? '';

// Second part of the path can be an ID for a specific resource
$id = $request[1] ?? null;

$response = [];

try {
    switch ($method) {
        case 'GET':
            if ($resource == 'users') {
                if ($id) {
                    // Get specific user
                    $stmt = $conn->prepare("SELECT user_id, username, is_admin, is_active, last_login, created_at FROM users WHERE user_id = ?");
                    $stmt->execute([$id]);
                    $response = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    // Get all users
                    $stmt = $conn->query("SELECT user_id, username, is_admin, is_active, last_login, created_at FROM users");
                    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } elseif ($resource == 'messages') {
                if ($id) {
                    // Get specific message
                    $stmt = $conn->prepare("SELECT * FROM messages WHERE message_id = ?");
                    $stmt->execute([$id]);
                    $response = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    // Get all messages
                    $stmt = $conn->query("SELECT * FROM messages");
                    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } elseif ($resource == 'settings') {
                if ($id) {
                    // Get settings for specific user
                    $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
                    $stmt->execute([$id]);
                    $response = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            } elseif ($resource == 'notes') {
                if ($id) {
                    // Get specific note
                    $stmt = $conn->prepare("SELECT * FROM notes WHERE note_id = ?");
                    $stmt->execute([$id]);
                    $response = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    // Get all notes for a user (requires user_id in query string)
                    $user_id = $_GET['user_id'] ?? null;
                    if ($user_id) {
                        $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $response = ['error' => 'User ID required for notes'];
                        http_response_code(400);
                    }
                }
            } else {
                $response = ['error' => 'Invalid resource requested'];
                http_response_code(404);
            }
            break;

        case 'POST':
            // Get JSON input
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($resource == 'users') {
                // Create new user
                if (isset($data['username']) && isset($data['password'])) {
                    $username = $data['username'];

                    // Salt and hash the password for security
                    $salt = bin2hex(random_bytes(16));
                    $password_hash = password_hash($salt . $data['password'], PASSWORD_DEFAULT);
                    $is_admin = $data['is_admin'] ?? 0;
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)");
                    
                    if ($stmt->execute([$username, $password_hash, $is_admin])) {
                        $response = [
                            'status' => 'success',
                            'message' => 'User created successfully',
                            'user_id' => $conn->lastInsertId()
                        ];
                        http_response_code(201); // Created
                    } else {
                        $response = ['error' => 'Failed to create user'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'Username and password required'];
                    http_response_code(400);
                }
            } elseif ($resource == 'messages') {
                // Create new message
                if (isset($data['user_id']) && isset($data['sender_name']) && 
                    isset($data['sender_email']) && isset($data['message_content'])) {
                    
                    $stmt = $conn->prepare("INSERT INTO messages (user_id, sender_name, sender_email, phone_number, subject, message_content) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([
                        $data['user_id'], 
                        $data['sender_name'], 
                        $data['sender_email'], 
                        $data['phone_number'] ?? null, 
                        $data['subject'] ?? null, 
                        $data['message_content']
                    ])) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Message sent successfully',
                            'message_id' => $conn->lastInsertId()
                        ];
                        http_response_code(201); // Created
                    } else {
                        $response = ['error' => 'Failed to send message'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'Missing required fields for message'];
                    http_response_code(400);
                }
            } elseif ($resource == 'settings') {
                // Create or update user settings
                if (isset($data['user_id'])) {
                    $stmt = $conn->prepare("INSERT INTO user_settings (user_id, terminal_color, audio_enabled) 
                                            VALUES (?, ?, ?) 
                                            ON DUPLICATE KEY UPDATE terminal_color = VALUES(terminal_color), audio_enabled = VALUES(audio_enabled)");
                    
                    if ($stmt->execute([
                        $data['user_id'], 
                        $data['terminal_color'] ?? 'green', 
                        $data['audio_enabled'] ?? 1
                    ])) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Settings saved successfully'
                        ];
                        http_response_code(201); // Created/Updated
                    } else {
                        $response = ['error' => 'Failed to save settings'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'User ID required for settings'];
                    http_response_code(400);
                }
            } elseif ($resource == 'notes') {
                // Create new note
                if (isset($data['user_id']) && isset($data['content'])) {
                    $stmt = $conn->prepare("INSERT INTO notes (user_id, content) VALUES (?, ?)");
                    
                    if ($stmt->execute([$data['user_id'], $data['content']])) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Note created successfully',
                            'note_id' => $conn->lastInsertId()
                        ];
                        http_response_code(201); // Created
                    } else {
                        $response = ['error' => 'Failed to create note'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'User ID and content required for notes'];
                    http_response_code(400);
                }
            } else {
                $response = ['error' => 'Invalid resource requested'];
                http_response_code(404);
            }
            break;

        case 'PUT':
            // Get JSON input
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($resource == 'users' && $id) {
                // Update user
                $updates = [];
                $params = [];
                
                if (isset($data['username'])) {
                    $updates[] = "username = ?";
                    $params[] = $data['username'];
                }
                
                if (isset($data['password'])) {
                    $updates[] = "password_hash = ?";
                    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                
                if (isset($data['is_admin'])) {
                    $updates[] = "is_admin = ?";
                    $params[] = $data['is_admin'];
                }
                
                if (isset($data['is_active'])) {
                    $updates[] = "is_active = ?";
                    $params[] = $data['is_active'];
                }
                
                if (!empty($updates)) {
                    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
                    $params[] = $id;
                    
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt->execute($params)) {
                        $response = [
                            'status' => 'success',
                            'message' => 'User updated successfully'
                        ];
                    } else {
                        $response = ['error' => 'Failed to update user'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'No fields to update'];
                    http_response_code(400);
                }
            } elseif ($resource == 'messages' && $id) {
                // Update message (marking as read is a common operation)
                if (isset($data['is_read'])) {
                    $stmt = $conn->prepare("UPDATE messages SET is_read = ? WHERE message_id = ?");
                    
                    if ($stmt->execute([$data['is_read'], $id])) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Message updated successfully'
                        ];
                    } else {
                        $response = ['error' => 'Failed to update message'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'No fields to update'];
                    http_response_code(400);
                }
            } elseif ($resource == 'settings' && isset($data['user_id'])) {
                // Update user settings
                $stmt = $conn->prepare("UPDATE user_settings SET terminal_color = ?, audio_enabled = ? WHERE user_id = ?");
                
                if ($stmt->execute([
                    $data['terminal_color'] ?? 'green', 
                    $data['audio_enabled'] ?? 1, 
                    $data['user_id']
                ])) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Settings updated successfully'
                    ];
                } else {
                    $response = ['error' => 'Failed to update settings'];
                    http_response_code(500);
                }
            } elseif ($resource == 'notes' && $id) {
                // Update note
                if (isset($data['content'])) {
                    $stmt = $conn->prepare("UPDATE notes SET content = ? WHERE note_id = ?");
                    
                    if ($stmt->execute([$data['content'], $id])) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Note updated successfully'
                        ];
                    } else {
                        $response = ['error' => 'Failed to update note'];
                        http_response_code(500);
                    }
                } else {
                    $response = ['error' => 'Content required to update note'];
                    http_response_code(400);
                }
            } else {
                $response = ['error' => 'Invalid resource or ID required'];
                http_response_code(404);
            }
            break;

        case 'DELETE':
            if ($resource == 'users' && $id) {
                // Delete user
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = [
                        'status' => 'success',
                        'message' => 'User deleted successfully'
                    ];
                } else {
                    $response = ['error' => 'Failed to delete user'];
                    http_response_code(500);
                }
            } elseif ($resource == 'messages' && $id) {
                // Delete message
                $stmt = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Message deleted successfully'
                    ];
                } else {
                    $response = ['error' => 'Failed to delete message'];
                    http_response_code(500);
                }
            } elseif ($resource == 'notes' && $id) {
                // Delete note
                $stmt = $conn->prepare("DELETE FROM notes WHERE note_id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Note deleted successfully'
                    ];
                } else {
                    $response = ['error' => 'Failed to delete note'];
                    http_response_code(500);
                }
            } else {
                $response = ['error' => 'Invalid resource or ID required'];
                http_response_code(404);
            }
            break;

        default:
            $response = ['error' => 'Invalid method'];
            http_response_code(405);
            break;
    }
} catch (Exception $e) {
    $response = ['error' => 'Server error: ' . $e->getMessage()];
    http_response_code(500);
}

echo json_encode($response);
$conn = null;
?>