<script lang="ts">
    import { onDestroy, tick, onMount } from 'svelte';
    import { get } from 'svelte/store';
    import '/src/app.css';
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import ColorSelector from '../components/colorselector.svelte';

    import { audioEnabled, terminalColor, lowGraphics, textSpeed } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let showContent = true;
    let currentStep = 1;
    let transitionInProgress = false;
    let inputEnabled = false;
    let pendingTransition: number | null = null;
    let showChoiceSelector = false;

    let localAudioEnabled: boolean = get(audioEnabled);
    let localTerminalColor: string = get(terminalColor);
    let localLowGraphics: boolean = get(lowGraphics);

    function loadCookies() {
        const cookies = document.cookie.split('; ');
        const audioCookie = cookies.find(row => row.startsWith('audioEnabled='));
        const colorCookie = cookies.find(row => row.startsWith('terminalColor='));
        const graphicsCookie = cookies.find(row => row.startsWith('lowGraphics='));

        if (audioCookie) localAudioEnabled = audioCookie.split('=')[1] === 'true';
        if (colorCookie) localTerminalColor = colorCookie.split('=')[1];
        if (graphicsCookie) localLowGraphics = graphicsCookie.split('=')[1] === 'true';

        audioEnabled.set(localAudioEnabled);
        terminalColor.set(localTerminalColor);
        lowGraphics.set(localLowGraphics);
    }

    function updateGlobalSettingsAndCookies() {
        audioEnabled.set(localAudioEnabled);
        terminalColor.set(localTerminalColor);
        lowGraphics.set(localLowGraphics);
        document.cookie = `audioEnabled=${localAudioEnabled}; path=/; max-age=31536000`;
        document.cookie = `terminalColor=${localTerminalColor}; path=/; max-age=31536000`;
        document.cookie = `lowGraphics=${localLowGraphics}; path=/; max-age=31536000`;
    }

    onMount(() => {
        if (typeof document !== 'undefined') {
            loadCookies();
            window.addEventListener('keydown', handleKeydown);
        }
    });

    function requestTransition(nextStep: number) {
        if (transitionInProgress) {
            if (nextStep < 0) {
                pendingTransition = nextStep;
            }
            return;
        }
        transitionInProgress = true;
        inputEnabled = false;
        showChoiceSelector = false;
        executeTransition(nextStep);
    }
    function executeTransition(nextStep: number) {
        showContent = false;

        setTimeout(() => {
            if (terminalSection) {
                terminalSection.innerHTML = '';
                currentStep = nextStep;
            }

            if (nextStep === -1) {
                updateGlobalSettingsAndCookies();
                navigateTo('login');
                return;
            } else if (nextStep === -2) {
                updateGlobalSettingsAndCookies();
                navigateTo('navigation');
                return;
            }

            showContent = true;

            setTimeout(() => {
                transitionInProgress = false;
                if (pendingTransition !== null) {
                    const next = pendingTransition;
                    pendingTransition = null;
                    executeTransition(next);
                }
            }, 1200);

        }, 1000);
    }

    function handleSelection(step: number, choiceIndex: number) {
        if (transitionInProgress || !inputEnabled || step !== currentStep) return;
        inputEnabled = false;

        switch(step) {
            case 1:
                if (choiceIndex === 0) requestTransition(-1);
                else if (choiceIndex === 1) requestTransition(2);
                break;
            case 2:
                if (choiceIndex === 0) requestTransition(3);
                else if (choiceIndex === 1) requestTransition(6);
                break;
            case 3:
                localLowGraphics = (choiceIndex === 0);
                requestTransition(4);
                break;
            case 4:
                localAudioEnabled = (choiceIndex === 0);
                requestTransition(5);
                break;
            default:
                 inputEnabled = true;
                break;
        }
    }

    function handleKeydown(event: KeyboardEvent) {
        if (!inputEnabled || transitionInProgress) return;

        if (event.key === 'Enter') {
            if (currentStep === 5) {
                inputEnabled = false;
                requestTransition(6);
            } else if (currentStep === 6) {
                inputEnabled = false;
                requestTransition(-2);
            }
        }
    }

    async function handleAnimationComplete(step: number) {
        if (transitionInProgress || step !== currentStep) return;

        if ([1, 2, 3, 4, 5].includes(step)) {
            await tick();
             if (!transitionInProgress && currentStep === step) {
                showChoiceSelector = true;
                inputEnabled = true;
                await tick();
                if (terminalSection) {
                    terminalSection.scrollTo({
                        top: terminalSection.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }
        } else if (step === 6) {
            inputEnabled = false;
            setTimeout(() => {
                 if (!transitionInProgress && currentStep === 6) {
                    requestTransition(-2);
                 }
            }, 1500);
        }
    }

    onDestroy(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('keydown', handleKeydown);
        }
    });
</script>

{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
    {#key currentStep}
        {#if currentStep === 1}
            <TextScroll audioPlay={localAudioEnabled} typingSpeed={50 / Number(get(textSpeed))} text="*************** PORTFOLIO-OS(R) V1.0.0 ***************" />
            <br><br>
            <TextScroll startDelay={1000} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="Setting up configuration..." />
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={700} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={1000} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="Logging In." />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(1)} />
            <br>

            {#if showChoiceSelector && currentStep === 1}
                <p class="choice-list">
                    <ChoiceSelector
                        choices={['Log In', 'Use Guest Mode']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(1, index)}
                    />
                </p>
            {/if}

        {:else if currentStep === 2}
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="Initializing Configuration..." />
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={30 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={1000} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="Customize Terminal?" />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(2)} />
            <br>

            {#if showChoiceSelector && currentStep === 2}
                <p class="choice-list">
                    <ChoiceSelector
                        choices={['Customize Terminal', 'Use Preconfigured Settings']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(2, index)}
                    />
                </p>
            {/if}

        {:else if currentStep === 3}
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={30 / Number(get(textSpeed))} text="Enable Low Graphics mode? (Better for low specs)" />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(3)} />
            <br>

            {#if showChoiceSelector && currentStep === 3}
                <p class="choice-list">
                    <ChoiceSelector
                        choices={['Enable', 'Disable']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(3, index)}
                    />
                </p>
            {/if}

        {:else if currentStep === 4}
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={1000} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={1000} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="Enable Audio?" />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(4)} />
            <br>

            {#if showChoiceSelector && currentStep === 4}
                <p class="choice-list">
                    <ChoiceSelector
                        choices={['Enable Audio', 'Disable Audio']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(4, index)}
                    />
                </p>
            {/if}

        {:else if currentStep === 5}
            <TextScroll startDelay={400} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="..." />
            <TextScroll startDelay={500} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text={localAudioEnabled ? "Audio Enabled" : "Audio Disabled"} />
            <TextScroll audioPlay={localAudioEnabled} typingSpeed={50 / Number(get(textSpeed))} text="Terminal Color Configuration" hideCaretManually={true} />
            <TextScroll startDelay={500} audioPlay={localAudioEnabled} typingSpeed={75 / Number(get(textSpeed))} text="Select a color:" />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(5)} />
             <br>

            {#if showChoiceSelector && currentStep === 5}
                 <div class="choice-list">
                    <ColorSelector
                        onColorSelect={(color) => {
                            localTerminalColor = color;
                        }}
                        onContinue={() => {
                            if (!transitionInProgress && inputEnabled) {
                                inputEnabled = false;
                                requestTransition(6);
                            }
                        }}
                        isActive={inputEnabled && !transitionInProgress}

                    />
                </div>
            {/if}

        {:else if currentStep >= 6}
            <TextScroll startDelay={200} audioPlay={localAudioEnabled} typingSpeed={50 / Number(get(textSpeed))} text="Configuration complete..." />
            <br>
            <TextScroll startDelay={500} audioPlay={localAudioEnabled} typingSpeed={50 / Number(get(textSpeed))} text="Welcome to PORTFOLIO-OS(R) V1.0.0!" />
            <br>
            <TextScroll startDelay={100} audioPlay={localAudioEnabled} typingSpeed={50 / Number(get(textSpeed))} text="Redirecting to main directory..." on:animationComplete={() => handleAnimationComplete(6)} />
        {/if}
    {/key}
</section>
{/if}