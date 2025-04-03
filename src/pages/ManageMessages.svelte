<script lang="ts">
    import { onMount } from 'svelte';
    import { navigateTo } from '../stores/routeStore';
    import { isLoggedIn, userID } from '../stores/globalStore';
    import ChoiceSelector from '../components/choiceselector.svelte';
  
    let messages: any[] = [];
    let selectedMessageId: number | null = null;
    let currentStep = 1;
    let messageDetails: any = null;
  
    let listChoices: string[] = [];
    let listCustomClasses: string[] = [];
  
    async function fetchMessages() {
      try {
        const response = await fetch('/api/messages');
        if (response.ok) {
          const fetchedMessages = await response.json();
          messages = fetchedMessages.sort((a: any, b: any) => {
            if (a.is_read === b.is_read) {
              return new Date(b.submitted_at).getTime() - new Date(a.submitted_at).getTime();
            }
            return a.is_read - b.is_read;
          });
          updateListChoices();
        } else {
          console.error('Failed to fetch messages:', await response.json());
        }
      } catch (error) {
        console.error('Error fetching messages:', error);
      }
    }
  
    function updateListChoices() {
      listChoices = ['Delete All', 'Back', '', ...messages.map(m => m.subject || 'No Subject')];
      listCustomClasses = [];
      listCustomClasses.push('');
      listCustomClasses.push('');
      listCustomClasses.push('separator');
      messages.forEach(m => {
        listCustomClasses.push(m.is_read ? 'read-message' : '');
      });
    }
  
    async function fetchMessageDetails(messageId: number) {
      try {
        const response = await fetch(`/api/messages/${messageId}`);
        if (response.ok) {
          messageDetails = await response.json();
        } else {
          console.error('Failed to fetch message details:', await response.json());
        }
      } catch (error) {
        console.error('Error fetching message details:', error);
      }
    }
  
    async function toggleReadStatus(messageId: number, currentStatus: boolean) {
      try {
        const response = await fetch(`/api/messages/${messageId}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ is_read: !currentStatus })
        });
        if (response.ok) {
          await fetchMessages();
          await fetchMessageDetails(messageId);
        } else {
          console.error('Failed to update read status:', await response.json());
        }
      } catch (error) {
        console.error('Error updating read status:', error);
      }
    }
  
    async function deleteMessage(messageId: number) {
      try {
        const response = await fetch(`/api/messages/${messageId}`, {
          method: 'DELETE'
        });
        if (response.ok) {
          await fetchMessages();
          currentStep = 1;
        } else {
          console.error('Failed to delete message:', await response.json());
        }
      } catch (error) {
        console.error('Error deleting message:', error);
      }
    }
  
    async function deleteAllMessages() {
      for (const message of messages) {
        await fetch(`/api/messages/${message.message_id}`, { method: 'DELETE' });
      }
      await fetchMessages();
      currentStep = 1;
    }
  
    function handleCancel() {
      currentStep = 1;
    }
  
    onMount(() => {
      if (!$isLoggedIn) {
        navigateTo('navigation');
        return;
      }
      fetch(`/api/users/${$userID}`)
        .then(res => res.json())
        .then(user => {
          if (!user.is_admin) {
            navigateTo('navigation');
          } else {
            fetchMessages();
          }
        })
        .catch(error => {
          console.error('Error checking admin status:', error);
          navigateTo('navigation');
        });
    });
  </script>
  
  {#if currentStep === 1}
    <ChoiceSelector 
      choices={listChoices}
      customClasses={listCustomClasses}
      isActive={true}
      onSelect={async (index) => {
        if (index === 0) {
          await deleteAllMessages();
        } else if (index === 1) {
          navigateTo('navigation');
        } else {
          selectedMessageId = messages[index - 3].message_id;
          await fetchMessageDetails(selectedMessageId);
          currentStep = 2;
        }
      }}
    />
  {:else if currentStep === 2}
    <div class="message-details">
      <p>
        <strong>Message ID:</strong> {messageDetails.message_id} &nbsp;&nbsp;
        <strong>User ID:</strong> {messageDetails.user_id}
      </p>
      <p>
        <strong>Sender:</strong> {messageDetails.sender_name}
        {messageDetails.phone_number ? ` + ${messageDetails.phone_number}` : ''}
      </p>
      <p>
        <strong>Email:</strong> {messageDetails.sender_email}
      </p>
      <p>
        <strong>Subject:</strong> {messageDetails.subject}
      </p>
      <p>
        <strong>Message:</strong> {messageDetails.message_content}
      </p>
    </div>
    <ChoiceSelector 
      choices={[`Mark: ${messageDetails.is_read ? 'Unread' : 'Read'}`, 'Delete', 'Back']}
      isActive={true}
      onSelect={async (index) => {
        if (index === 0) {
          await toggleReadStatus(selectedMessageId, messageDetails.is_read);
        } else if (index === 1) {
          await deleteMessage(selectedMessageId);
        } else if (index === 2) {
          handleCancel();
        }
      }}
    />
  {/if}
  
  <style>
    .message-details {
      margin-bottom: 1rem;
    }
    :global(.read-message) {
      color: #999898 !important;
    }
    .separator {
      height: 10px;
      pointer-events: none;
    }
  </style>