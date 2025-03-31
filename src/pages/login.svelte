<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import { audioEnabled, terminalColor, isLoggedIn, userID } from '../stores/globalStore';
    import ColorFilter from '../components/colorfilter.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { navigateTo } from '../stores/routeStore';
    import { get } from 'svelte/store';
    import { fly } from 'svelte/transition';

    let username = '';
    let password = '';
    let errorMessage = '';
    let choiceList: HTMLParagraphElement;
    let isChoiceActive = true;
    let choiceSelectorRef: { reactivate: () => void }; // Reference to ChoiceSelector
    
    let showContent = true;
    let terminalSection: HTMLElement;

    function clearTerminal() {
        showContent = false;
        setTimeout(() => {
            if (terminalSection) {
                terminalSection.innerHTML = '';
            }
            showContent = true;
        }, 1000);
    }

    async function handleLogin() {
        if (!username || !password) {
            errorMessage = 'Username and password are required';
            choiceSelectorRef.reactivate(); // Reactivate ChoiceSelector
            return;
        }
        isChoiceActive = false;
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
                        choiceSelectorRef.reactivate(); // Reactivate ChoiceSelector
                        isChoiceActive = true;
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
                // Handle server errors (e.g., 401 Unauthorized)
                errorMessage = data.error || 'Login failed';
                choiceSelectorRef.reactivate(); // Reactivate ChoiceSelector
                isChoiceActive = true;
            }
        } catch (error) {
            // Handle unexpected errors
            errorMessage = 'An error occurred during login';
            choiceSelectorRef.reactivate(); // Reactivate ChoiceSelector
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
    <section class="terminal-login" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
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
            <p style="color: #c8c8c8; font-size: 1rem;">{errorMessage}</p>
        {/if}

        <p class="choice-list" bind:this={choiceList} style="visibility: visible;">
            <ChoiceSelector 
                bind:this={choiceSelectorRef}
                choices={['Log In', 'Create Account', 'Back']} 
                bind:isActive={isChoiceActive} 
                onSelect={(index) => {
                    if (index === 0) {
                        if (!username || !password) {
                            errorMessage = 'Username and password are required';
                            choiceSelectorRef.reactivate(); // Reactivate ChoiceSelector
                            return;
                        }
                        isChoiceActive = false;
                        handleLogin();
                    } else if (index === 1) {
                        handleCreateAccount();
                    } else if (index === 2) {
                        clearTerminal();
                        navigateTo('mainConfig');
                    }
                }}
            />
        </p>
    </section>
</ColorFilter>