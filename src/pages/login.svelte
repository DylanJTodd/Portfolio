<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import { audioEnabled, terminalColor } from '../stores/globalStore';
    import ColorFilter from '../components/colorfilter.svelte';
    import { navigateTo } from '../stores/routeStore';

    let username = '';
    let password = '';
    let errorMessage = '';

    async function handleLogin() {
        if (!username || !password) {
            errorMessage = 'Username and password are required';
            return;
        }
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (response.ok) {
                navigateTo('navigation');
            } else {
                errorMessage = data.error || 'Login failed';
            }
        } catch (error) {
            errorMessage = 'An error occurred during login';
            console.error(error);
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
                navigateTo('navigation');
            } else {
                errorMessage = data.error || 'Account creation failed';
            }
        } catch (error) {
            errorMessage = 'An error occurred during account creation';
            console.error(error);
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

        <button on:click={handleLogin} style="font-size: 1rem; padding: 0.1rem; margin-right: 1rem;">LOGIN</button>
        <button on:click={handleCreateAccount} style="font-size: 1rem; padding: 0.1rem;">CREATE ACCOUNT</button>
    </section>
</ColorFilter>