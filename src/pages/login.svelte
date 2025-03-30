<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import { audioEnabled, terminalColor, isLoggedIn, userID } from '../stores/globalStore';
    import ColorFilter from '../components/colorfilter.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { navigateTo } from '../stores/routeStore';
    import { get } from 'svelte/store';

    let username = '';
    let password = '';
    let errorMessage = '';
    let choiceList: HTMLParagraphElement;
    let isChoiceActive = true;

    async function handleLogin() {
        if (!username || !password) {
            errorMessage = 'Username and password are required';
            return;
        }
        try {
            const requestData = { username, password };

            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (response.ok) {
                const userResponse = await fetch(`/api/users?username=${username}`);
                if (userResponse.ok) {
                    const userData = await userResponse.json();
                    const userId = userData.user_id;

                    if (!userId) {
                        errorMessage = 'Failed to retrieve user ID';
                        return;
                    }

                    const settingsResponse = await fetch(`/api/settings/${userId}`);
                    if (settingsResponse.ok) {
                        const settings = await settingsResponse.json();

                        if (settings && typeof settings.terminal_color === 'string' && settings.audio_enabled !== undefined) {
                            terminalColor.set(settings.terminal_color);
                            audioEnabled.set(!!settings.audio_enabled);

                            document.cookie = `terminal_color=${settings.terminal_color}; path=/`;
                            document.cookie = `audio_enabled=${settings.audio_enabled}; path=/`;
                        }
                    }
                }
                isLoggedIn.set(true);
                userID.set(data.user_id);
                navigateTo('navigation');
            } else {
                errorMessage = data.error || 'Login failed';
                isChoiceActive = true;
            }
        } catch (error) {
            errorMessage = 'An error occurred during login';
            isChoiceActive = true;
        }
    }

    async function handleCreateAccount() {
        if (!username || !password) {
            errorMessage = 'Username and password are required';
            return;
        }
        if (password.length < 8 || password.length > 64) {
            errorMessage = 'Password must be between 8 and 64 characters';
            return;
        }
        try {
            const response = await fetch('/api/signup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (response.ok) {
                await fetch('/api/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: data.user_id,
                        terminal_color: get(terminalColor),
                        audio_enabled: get(audioEnabled)
                    })
                });
                navigateTo('navigation');
            } else {
                errorMessage = data.error || 'Account creation failed';
            }
        } catch (error) {
            errorMessage = 'An error occurred during account creation';
        }
    }
</script>

<ColorFilter>
    <section class="terminal-login">
        <p style="color: yellow; font-size: 0.9rem; margin-bottom: 1rem;">
            Please don't share private information anywhere on this website, including a real username or password. This is meant for fun not as a secure storage.
        </p>
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

        {#if errorMessage}
            <p style="color: red; font-size: 1rem;">{errorMessage}</p>
        {/if}

        <p class="choice-list" bind:this={choiceList} style="visibility: visible;">
            <ChoiceSelector 
                choices={['Log In', 'Create Account', 'Back']} 
                bind:isActive={isChoiceActive} 
                onSelect={(index) => {
                    if (index === 0) {
                        isChoiceActive = false;
                        handleLogin();
                    } else if (index === 1) {
                        handleCreateAccount();
                    } else if (index === 2) {
                        navigateTo('mainConfig');
                    }
                }}
            />
        </p>
    </section>
</ColorFilter>