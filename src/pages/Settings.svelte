<script lang="ts">
  import TextScroll from '../components/textscroll.svelte';
  import ChoiceSelector from '../components/choiceselector.svelte';
  import ColorSelector from '../components/colorselector.svelte';
  import { audioEnabled, audioLevel, terminalColor, textSpeed, fontSize, userID, lowGraphics } from '../stores/globalStore';
  import { onMount } from 'svelte';
  import { navigateTo } from '../stores/routeStore';
  import { fly } from 'svelte/transition';
  import { get } from 'svelte/store';

  let currentStep = 1;
  let isChoiceActive = true;
  let showColorSelector = false;
  let selectedIndex = -1;
  let pendingColor: string | null = null;
  let terminalSection: HTMLElement;
  let showContent = true;

  function clearTerminal() {
    showContent = false;
    setTimeout(() => {
      if (terminalSection) {
        terminalSection.innerHTML = '';
      }
      showContent = true;
    }, 1000);
  }

  async function updateSettings(newSettings: Partial<{
    terminal_color: string;
    audio_enabled: boolean;
    font_size: number;
    text_speed: number;
    audio_level: number;
    lowGraphics: boolean;
  }>) {
    const uid = get(userID);
    if (!uid || uid === '') {
      console.error('User ID is not available');
      return;
    }
    
    const currentLowGraphics = get(lowGraphics);
    
    const settingsToSend = {
      terminal_color: get(terminalColor),
      audio_enabled: get(audioEnabled) ? 1 : 0,
      font_size: get(fontSize),
      text_speed: get(textSpeed),
      audio_level: get(audioLevel),
      lowGraphics: currentLowGraphics ? 1 : 0,
      ...newSettings
    };
    
    if ('lowGraphics' in newSettings) {
      settingsToSend.lowGraphics = newSettings.lowGraphics ? 1 : 0;
    }
    
    try {
      const response = await fetch(`/api/settings/${uid}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settingsToSend),
        credentials: 'include'
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        console.error('Settings update failed:', errorData);
        throw new Error(errorData.error || 'Failed to update settings');
      }
    } catch (error) {
      console.error('Error updating setting:', error);
    }
  }

  function handleAudioToggle() {
    const newAudioEnabled = !get(audioEnabled);
    audioEnabled.set(newAudioEnabled);
    updateSettings({ audio_enabled: newAudioEnabled });
  }

  function handleAudioLevelChange(value: number) {
    if (value < 0 || value > 100) {
      alert('Audio level must be between 0 and 100.');
      return;
    }
    audioLevel.set(value);
    updateSettings({ audio_level: value });
  }

  function handleTextSpeedChange(value: number) {
    if (value < 0.25 || value > 2) {
      alert('Text speed multiplier must be between 0.25 and 2.');
      return;
    }
    textSpeed.set(value);
    updateSettings({ text_speed: value });
  }

  function handleFontSizeChange(value: number) {
    if (value < 0.25 || value > 2) {
      alert('Font size multiplier must be between 0.25 and 2.');
      return;
    }
    fontSize.set(value);
    updateSettings({ font_size: value });
  }

  function handleColorSelect(color: string) {
    pendingColor = color;
    terminalColor.set(color);
    updateSettings({ terminal_color: color });
  }

  function handleColorSelectorContinue() {
    pendingColor = null;
    showColorSelector = false;
    isChoiceActive = true;
  }
  
  function handleLowGraphicsToggle() {
    const newLowGraphics = !get(lowGraphics);
    lowGraphics.set(newLowGraphics);
    updateSettings({ lowGraphics: newLowGraphics === true });
  }
</script>

{#if showContent}
  <section class="terminal-settings" bind:this={terminalSection} in:fly={{ y: 0, duration: 1000 }} out:fly={{ y: -1000, duration: 1000 }}>
    {#if currentStep === 1}
      <TextScroll audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="Customize your settings" />
      <TextScroll hideCaretManually={true} startDelay={500} audioPlay={$audioEnabled} /><br>
      <div>
        <ChoiceSelector
          choices={[
            `Audio: ${$audioEnabled ? 'Enabled' : 'Disabled'}`,
            `Audio Level: ${$audioLevel}%`,
            'Terminal Color',
            `Text Speed: ${$textSpeed}x`,
            `Font Size: ${$fontSize}x`,
            `Low Graphics: ${$lowGraphics ? 'Enabled' : 'Disabled'}`,
            'Back'
          ]}
          isActive={isChoiceActive}
          settingsMode={true}
          exitIndex={6}
          onSelect={(index) => {
            selectedIndex = index;
            if (index === 0) {
              handleAudioToggle();
            } else if (index === 1) {
              const newLevel = parseFloat(prompt('Enter audio level (0-100):', $audioLevel.toString()) || $audioLevel.toString());
              if (!isNaN(newLevel)) {
                handleAudioLevelChange(newLevel);
              }
            } else if (index === 2) {
              showColorSelector = true;
              isChoiceActive = false;
            } else if (index === 3) {
              const newSpeed = parseFloat(prompt('Enter text speed multiplier (0.25-2):', $textSpeed.toString()) || $textSpeed.toString());
              if (!isNaN(newSpeed)) {
                handleTextSpeedChange(newSpeed);
              }
            } else if (index === 4) {
              const newSize = parseFloat(prompt('Enter font size multiplier (0.25-2):', $fontSize.toString()) || $fontSize.toString());
              if (!isNaN(newSize)) {
                handleFontSizeChange(newSize);
              }
            } else if (index === 5) {
              handleLowGraphicsToggle();
            } else if (index === 6) {
              clearTerminal();
              navigateTo('navigation');
            }
          }}
        />
        {#if showColorSelector}
          <div>
            <ColorSelector
              onColorSelect={handleColorSelect}
              onContinue={handleColorSelectorContinue}
              isActive={showColorSelector}
            />
          </div>
        {/if}
      </div>
    {/if}
  </section>
{/if}