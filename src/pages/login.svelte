<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import { audioEnabled, terminalColor } from '../stores/globalStore';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import ColorFilter from '../components/colorfilter.svelte';
    import { navigateTo } from '../stores/routeStore';

    let username = '';
    let password = '';
    let confirmPassword = '';
    let errorMessage = '';
    let choiceList: HTMLParagraphElement;
    let showContent = true;
    let currentStep = 1;

    function generateSalt(username: string): string {
        // Duplicates every second character from username and shifts its ascii value by 2
        return username
            .split('')
            .map((char, index) => (index % 2 === 0 ? char : String.fromCharCode(char.charCodeAt(0) + 2) + char))
            .join('');
    }

    async function hashPassword(password: string, username: string): Promise<string> {
        const salt = generateSalt(username);
        const encoder = new TextEncoder();
        const data = encoder.encode(salt + password);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        return Array.from(new Uint8Array(hashBuffer))
            .map(byte => byte.toString(16).padStart(2, '0'))
            .join('');
    }

    async function handleLogin() {
        if (password.length < 8 || password.length > 64) {
            errorMessage = 'Password must be between 8 and 64 characters';
            return;
        }
        try {
            const response = await fetch(`/backend/api.php/users?username=${encodeURIComponent(username)}`);
            const data = await response.json();

            if (data.user_id) {
                const hashedPassword = await hashPassword(password, username);
                if (data.password_hash === hashedPassword) {
                    sessionStorage.setItem('userId', data.user_id);
                    sessionStorage.setItem('username', username);

                    const settingsResponse = await fetch(`/backend/api.php/settings/${data.user_id}`);
                    const settingsData = await settingsResponse.json();
                    if (settingsData) {
                        audioEnabled.set(settingsData.audio_enabled === 1);
                        terminalColor.set(settingsData.terminal_color);

                        document.cookie = `audioEnabled=${settingsData.audio_enabled === 1}; path=/;`;
                        document.cookie = `terminalColor=${settingsData.terminal_color}; path=/;`;
                    }

                    navigateTo('navigation');
                } else {
                    errorMessage = 'Invalid username or password';
                }
            } else {
                errorMessage = 'Invalid username or password';
            }
        } catch (error) {
            errorMessage = 'An error occurred during login';
            console.error(error);
        }
    }

    async function handleCreateAccount() {
        if (password.length < 8 || password.length > 64) {
            errorMessage = 'Password must be between 8 and 64 characters';
            return;
        }
        if (password !== confirmPassword) {
            errorMessage = 'Passwords do not match';
            return;
        }
        try {
            const hashedPassword = await hashPassword(password, username);
            const response = await fetch('/backend/api.php/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    username, 
                    password: password // Send raw password for hashing in the backend
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                sessionStorage.setItem('userId', data.user_id);
                sessionStorage.setItem('username', username);

                // Fetch and save default settings for the new user
                const settingsResponse = await fetch(`/backend/api.php/settings`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: data.user_id,
                        terminal_color: $terminalColor,
                        audio_enabled: $audioEnabled ? 1 : 0
                    })
                });

                if (settingsResponse.ok) {
                    navigateTo('navigation');
                } else {
                    errorMessage = 'Failed to save user settings';
                }
            } else {
                errorMessage = data.error || 'Account creation failed';
            }
        } catch (error) {
            errorMessage = 'An error occurred during account creation';
            console.error(error);
        }
    }

    function clearTerminal() {
        showContent = false;
        setTimeout(() => {
            currentStep += 1;
            showContent = true;
        }, 1000);
    }

    async function handleChoice(index: number) {
        if (index === 0) {
            await handleLogin();
        } else if (index === 1) {
            clearTerminal();
        } else if (index === 2) {
            navigateTo('MainConfig');
        }
    }
</script>

<ColorFilter>
    {#if showContent}
    <section class="terminal-login">
        {#if currentStep === 1}
            <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="LOGIN TERMINAL"/><br><br>
            <label for="username">USERNAME:</label>
            <input 
                type="text" 
                id="username" 
                bind:value={username} 
                style="background-color: #333; color: white; font-size: 1rem; padding: 0.1rem; margin-bottom: 1rem; width: 80%;"
            /><br>
            
            <label for="password">PASSWORD:</label>
            <input 
                type="password" 
                id="password" 
                bind:value={password} 
                style="background-color: #333; color: white; font-size: 1rem; padding: 0.1rem; margin-bottom: 1rem; width: 80%;"
            /><br>

            <label for="confirm-password">CONFIRM PASSWORD:</label>
            <input 
                type="password" 
                id="confirm-password" 
                bind:value={confirmPassword} 
                style="background-color: #333; color: white; font-size: 1rem; padding: 0.1rem; margin-bottom: 1rem; width: 80%;"
            /><br>

            {#if errorMessage}
                <p style="color: red; font-size: 1rem;">{errorMessage}</p>
            {/if}

            <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={50} text="Choose an option:" hideCaretManually={true} on:animationComplete={() => {
                if (choiceList) {
                    choiceList.style.visibility = 'visible';
                }
            }}/>

            <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
                <ChoiceSelector 
                    choices={['Login', 'Create Account', 'Back to Configuration']} 
                    isActive={choiceList?.style.visibility === 'visible'}
                    onSelect={handleChoice}
                />
            </p>

        {:else if currentStep === 2}
            <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Creating Account..."/><br>
            <button on:click={handleCreateAccount} style="font-size: 1rem; padding: 0.1rem; margin-right: 1rem;">CREATE ACCOUNT</button>
        {/if}
    </section>
    {/if}
</ColorFilter>