<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import ColorSelector from '../components/colorselector.svelte';
    import { audioEnabled, audioLevel, terminalColor, textSpeed, fontSize, userID } from '../stores/globalStore';
    import { onMount } from 'svelte';
    import { navigateTo } from '../stores/routeStore';
    import { fly } from 'svelte/transition';

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
    
    async function updateSetting(setting: string, value: any) {
        if (!$userID || $userID === '') {
            console.error('User ID is not available');
            return;
        }
        try {
            await fetch(`/api/settings/${$userID}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ [setting]: value })
            });
        } catch (error) {
            console.error('Error updating setting:', error);
        }
    }

    function handleAudioToggle() {
        $audioEnabled = !$audioEnabled;
        updateSetting('audio_enabled', $audioEnabled);
    }

    function handleAudioLevelChange(value: number) {
        $audioLevel = value;
        updateSetting('audio_level', $audioLevel);
    }

    function handleTextSpeedChange(value: number) {
        $textSpeed = value;
        updateSetting('text_speed', $textSpeed);
    }

    function handleFontSizeChange(value: number) {
        $fontSize = value;
        updateSetting('font_size', $fontSize);
    }

    function handleColorSelect(color: string) {
        pendingColor = color;
        $terminalColor = pendingColor;
        updateSetting('terminal_color', $terminalColor);
    }

    function handleColorSelectorContinue() {
        pendingColor = null;
        showColorSelector = false;
        isChoiceActive = true;
    }
</script>

{#if showContent}
<section class="terminal-settings" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}"  out:fly="{{ y: -1000, duration: 1000 }}">
    {#if currentStep === 1}
        <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Customize your settings" />
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={$audioEnabled} /><br>
        <div>
            <ChoiceSelector
                choices={[
                    `Audio: ${$audioEnabled ? 'Enabled' : 'Disabled'}`,
                    `Audio Level: ${$audioLevel}%`,
                    'Terminal Color',
                    `Text Speed: ${$textSpeed}x`,
                    `Font Size: ${$fontSize}x`,
                    'Back'
                ]}
                isActive={isChoiceActive}
                settingsMode={true}
                exitIndex={5}
                onSelect={(index) => {
                    selectedIndex = index;
                    if (index === 0) handleAudioToggle();
                    if (index === 1) handleAudioLevelChange(parseFloat(prompt('Enter audio level (0-100):', $audioLevel.toString()) || $audioLevel.toString()));
                    if (index === 2) {
                        showColorSelector = true;
                        isChoiceActive = false;
                    }
                    if (index === 3) handleTextSpeedChange(parseFloat(prompt('Enter text speed multiplier (0.25-2):', $textSpeed.toString()) || $textSpeed.toString()));
                    if (index === 4) handleFontSizeChange(parseFloat(prompt('Enter font size multiplier (0.25-2):', $fontSize.toString()) || $fontSize.toString()));
                    if (index === 5) {clearTerminal(); navigateTo('navigation');}
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