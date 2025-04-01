<script lang="ts">
    import ColorFilter from '../components/colorfilter.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { fly } from 'svelte/transition';
    import { get } from 'svelte/store';
    import { audioEnabled, terminalColor, fontSize, textSpeed, audioLevel, isLoggedIn, userID } from '../stores/globalStore';
    import { navigateTo } from '../stores/routeStore';
  
    let username = '';
    let password = '';
    let errorMessage = '';
    let choiceList: HTMLParagraphElement;
    let isChoiceActive = true;
    let choiceSelectorRef: { reactivate: () => void };
  
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
  
    async function loadUserSettings(userId: string) {
      try {
        const settingsResponse = await fetch(`/api/settings/${userId}`);
        if (settingsResponse.ok) {
          const settings = await settingsResponse.json();
          if (settings) {
            if (typeof settings.terminal_color === 'string')
              terminalColor.set(settings.terminal_color);
            if (settings.audio_enabled !== undefined)
              audioEnabled.set(!!settings.audio_enabled);
            if (settings.text_speed !== undefined)
              textSpeed.set(settings.text_speed);
            if (settings.font_size !== undefined)
              fontSize.set(settings.font_size);
            if (settings.audio_level !== undefined)
              audioLevel.set(settings.audio_level);
  
            // Save settings into cookies so that later pages can read them if necessary
            document.cookie = `terminal_color=${settings.terminal_color}; path=/;`;
            document.cookie = `audio_enabled=${settings.audio_enabled}; path=/;`;
            document.cookie = `text_speed=${settings.text_speed}; path=/;`;
            document.cookie = `font_size=${settings.font_size}; path=/;`;
            document.cookie = `audio_level=${settings.audio_level}; path=/;`;
            
            return true;
          }
        }
        return false;
      } catch (error) {
        console.error('Error loading settings:', error);
        return false;
      }
    }
  
    async function handleLogin() {
      if (!username || !password) {
        errorMessage = 'Username and password are required';
        if (choiceSelectorRef) choiceSelectorRef.reactivate();
        return;
      }
  
      isChoiceActive = false;
  
      try {
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        if (response.ok && data.user_id) {
          // load user settings after login
          const settingsLoaded = await loadUserSettings(data.user_id);
          if (!settingsLoaded) {
            errorMessage = 'Failed to load user settings, but login was successful.';
            // Continue the login process even if settings fail to load
          }
          userID.set(data.user_id);
          isLoggedIn.set(true);
          navigateTo('navigation');
        } else {
          errorMessage = data.error || 'Login failed';
          isChoiceActive = true;
          if (choiceSelectorRef) choiceSelectorRef.reactivate();
        }
      } catch (error) {
        console.error('Login error:', error);
        errorMessage = 'An error occurred during login';
        isChoiceActive = true;
        if (choiceSelectorRef) choiceSelectorRef.reactivate();
      }
    }
  
    async function handleCreateAccount() {
      if (!username || !password) {
        errorMessage = 'Username and password are required';
        if (choiceSelectorRef) choiceSelectorRef.reactivate();
        return;
      }
  
      if (password.length < 8 || password.length > 64) {
        errorMessage = 'Password must be between 8 and 64 characters';
        if (choiceSelectorRef) choiceSelectorRef.reactivate();
        return;
      }
  
      isChoiceActive = false;
  
      try {
        const response = await fetch('/api/signup', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        if (response.ok) {
          // After a successful signup, the API might not return a user_id.
          let userId = data.user_id;
          if (!userId) {
            // Assuming the session is now set, fetch user details to get the user_id.
            const userResponse = await fetch(`/api/users?username=${encodeURIComponent(username)}`);
            const userData = await userResponse.json();
            if (userResponse.ok && userData && userData.user_id) {
              userId = userData.user_id;
            } else {
              errorMessage = 'Failed to retrieve user ID after signup';
              isChoiceActive = true;
              if (choiceSelectorRef) choiceSelectorRef.reactivate();
              return;
            }
          }
  
          // Prepare default values ensuring numeric types where needed.
          const defaultSettings = {
            user_id: userId,
            // If no terminal color is set, we use the hex value (matching your table default)
            terminal_color: get(terminalColor) || '#00ff00',
            // Convert the boolean to 1 (true) or 0 (false)
            audio_enabled: get(audioEnabled) ? 1 : 0,
            text_speed: get(textSpeed) || 1,
            font_size: get(fontSize) || 1,
            audio_level: get(audioLevel) || 100
          };
  
          try {
            const settingsResponse = await fetch('/api/settings', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(defaultSettings)
            });
            if (!settingsResponse.ok) {
              console.error('Failed to save initial settings');
            }
          } catch (settingsError) {
            console.error('Error saving initial settings:', settingsError);
          }
  
          // Set global stores and navigate
          userID.set(userId);
          isLoggedIn.set(true);
          navigateTo('navigation');
        } else {
          errorMessage = data.error || 'Account creation failed';
          isChoiceActive = true;
          if (choiceSelectorRef) choiceSelectorRef.reactivate();
        }
      } catch (error) {
        console.error('Account creation error:', error);
        errorMessage = 'An error occurred during account creation';
        isChoiceActive = true;
        if (choiceSelectorRef) choiceSelectorRef.reactivate();
      }
    }
  </script>
  
  <ColorFilter>
    <section 
      class="terminal-login" 
      bind:this={terminalSection} 
      in:fly={{ y: 0, duration: 1000 }} 
      out:fly={{ y: -1000, duration: 1000 }}
    >
      <p style="color: yellow; font-size: 0.9rem; margin-bottom: 1rem;">
        Please don't share private information anywhere on this website, including a real username or password. This is meant for fun not as secure storage.
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