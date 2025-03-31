<script lang="ts">
    import { onDestroy } from 'svelte';
    import '/src/app.css';
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import ColorSelector from '../components/colorselector.svelte';

    import { audioEnabled, terminalColor } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let showContent = true;
    let currentStep = 1;
    let transitionInProgress = false;
    let inputEnabled = false;
    let pendingTransition: number | null = null;
    let showChoiceSelector = false;

    function loadCookies() {
        const audioCookie = document.cookie.split('; ').find(row => row.startsWith('audioEnabled='));
        const colorCookie = document.cookie.split('; ').find(row => row.startsWith('terminalColor='));
        if (audioCookie) audioEnabled.set(audioCookie.split('=')[1] === 'true');
        if (colorCookie) terminalColor.set(colorCookie.split('=')[1]);
    }

    function updateCookies() {
        document.cookie = `audioEnabled=${$audioEnabled}; path=/;`;
        document.cookie = `terminalColor=${$terminalColor}; path=/;`;
    }

    if (typeof window !== 'undefined') {
        loadCookies();
    }

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
                updateCookies();
                navigateTo('login');
                return;
            } else if (nextStep === -2) {
                updateCookies();
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
        
        console.log("index: " + choiceIndex);
        console.log("step: " + currentStep);

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
                $audioEnabled = (choiceIndex === 0);
                requestTransition(4);
                break;
            default:
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
                updateCookies();
                requestTransition(-2);
            }
        }
    }

    function handleAnimationComplete(step: number) {
        if (transitionInProgress || step !== currentStep) return;
        
        if ([1, 2, 3, 4].includes(step)) {
            setTimeout(() => {
                if (!transitionInProgress && currentStep === step) {
                    showChoiceSelector = true;
                    inputEnabled = true;
                }
            }, 100);
        } else if (step === 5) {
            setTimeout(() => {
                if (!transitionInProgress && currentStep === step) {
                    inputEnabled = true;
                }
            }, 100);
        } else if (step === 6) {
            inputEnabled = false;
            setTimeout(() => {
                updateCookies();
                requestTransition(-2);
            }, 2000);
        }
    }

    if (typeof window !== 'undefined') {
        window.addEventListener('keydown', handleKeydown);
    }

    onDestroy(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('keydown', handleKeydown);
        }
    });
</script>

{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">

    {#if currentStep === 1}
        <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="*************** PORTFOLIO-OS(R) V1.0.0 ***************" /><br><br>
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Setting up configuration..." />
        <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={700} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Logging In." />
        <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} on:animationComplete={() => handleAnimationComplete(1)} /><br>
        
        {#if showChoiceSelector && currentStep === 1}
            {#key currentStep}
                <p class="choice-list">
                    <ChoiceSelector 
                        choices={['Log In', 'Use Guest Mode']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(1, index)}
                    />
                </p>
            {/key}
        {/if}

    {:else if currentStep === 2}
        <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="Initializing Configuration..." />
        <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={30} text="..." />
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Customize Terminal?" />
        <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} on:animationComplete={() => handleAnimationComplete(2)} /><br>

        {#if showChoiceSelector && currentStep === 2}
            {#key currentStep}
                <p class="choice-list">
                    <ChoiceSelector 
                        choices={['Customize Terminal', 'Use Preconfigured Settings']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(2, index)}
                    />
                </p>
            {/key}
        {/if}

    {:else if currentStep === 3}
        <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Enable Audio?" />
        <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} on:animationComplete={() => handleAnimationComplete(3)} /><br>

        {#if showChoiceSelector && currentStep === 3}
            {#key currentStep}
                <p class="choice-list">
                    <ChoiceSelector 
                        choices={['Enable Audio', 'Disable Audio']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(3, index)}
                    />
                </p>
            {/key}
        {/if}

    {:else if currentStep === 4}    
        <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={75} text={$audioEnabled ? "Audio Enabled" : "Audio Disabled"} />
        <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Terminal Color Configuration" hideCaretManually={true} /><br>
        <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={75} text="Enter a color:" />
        <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} on:animationComplete={() => handleAnimationComplete(4)} /><br>

        {#if showChoiceSelector && currentStep === 4}
            {#key currentStep}
                <p class="choice-list">
                    <ColorSelector 
                        onColorSelect={(color) => {
                            $terminalColor = color;
                        }}
                        onContinue={() => {
                            if (!transitionInProgress) {
                                inputEnabled = false;
                                requestTransition(5);
                            }
                        }}
                        isActive={inputEnabled && !transitionInProgress}
                    />
                </p>
            {/key}
        {/if}

    {:else if currentStep === 5}
        <TextScroll audioPlay={$audioEnabled} typingSpeed={75} text="Colors configured." />
        <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..." />
        <TextScroll startDelay={800} audioPlay={$audioEnabled} typingSpeed={100} text="... OK" on:animationComplete={() => handleAnimationComplete(5)} />

    {:else if currentStep >= 6}
        <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={50} text="Configuration complete..." /><br>
        <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={50} text="Welcome to PORTFOLIO-OS(R) V1.0.0!" /><br>
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={50} text="Redirecting to main directory..." on:animationComplete={() => handleAnimationComplete(6)} />
    {/if}
</section>
{/if}