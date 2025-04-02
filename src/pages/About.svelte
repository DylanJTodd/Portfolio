<script lang="ts">
    import { onDestroy } from 'svelte';
    import '/src/app.css';
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { audioEnabled, textSpeed } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let showContent = true;
    let currentStep = 1;
    let transitionInProgress = false;
    let inputEnabled = false;
    let pendingTransition: number | null = null;
    let showChoiceSelector = false;

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
                navigateTo('login');
                return;
            } else if (nextStep === -2) {
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

        if (currentStep === 1) {
            switch (choiceIndex) {
                case 0:
                    requestTransition(2);
                    break;
                case 1:
                    window.open('https://github.com/DylanJTodd/Portfolio', '_blank');
                    inputEnabled = true;
                    showChoiceSelector = true;
                    break;
                case 2:
                    requestTransition(currentStep);
                    navigateTo('navigation');
                    break;
                default:
                    break;
            }
        } else if (currentStep === 2) {
            switch (choiceIndex) {
                case 0:
                    window.open('https://github.com/DylanJTodd/Portfolio', '_blank');
                    inputEnabled = true;
                    showChoiceSelector = true;
                    break;
                case 1:
                    window.open('https://www.linkedin.com/in/dylan-j-todd/', '_blank');
                    inputEnabled = true;
                    showChoiceSelector = true;
                    break;
                case 2:
                    requestTransition(1);
                    break;
                case 3:
                    requestTransition(currentStep);
                    navigateTo('navigation');
                    break;
                default:
                    break;
            }
        }
    }

    function handleKeydown(event: KeyboardEvent) {
        if (!inputEnabled || transitionInProgress) return;
    }

    function handleAnimationComplete(step: number) {
        if (transitionInProgress || step !== currentStep) return;
        
        if ([1, 2].includes(step)) {
            setTimeout(() => {
                if (!transitionInProgress && currentStep === step) {
                    showChoiceSelector = true;
                    inputEnabled = true;
                }
            }, 100);
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
        <TextScroll audioPlay={$audioEnabled} typingSpeed={30 * Number($textSpeed)} text="Hello! My name is Dylan Todd. Thank you for viewing my portfolio website." /><br><br>
        <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 * Number($textSpeed)} text="This website was created for a web design class in my 3rd year of university, inspired by the Fallout 4 terminal." />
        <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 * Number($textSpeed)} text="It uses the LAMP stack—Linux, Apache, MySQL, and PHP—with Apache hosted via XAMPP and MySQL managed through phpMyAdmin." />
        <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 * Number($textSpeed)} text="My frontend was built using SvelteKit with TypeScript. Although I initially planned to use TailwindCSS, course requirements led me to another approach." />
        <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} on:animationComplete={() => handleAnimationComplete(1)} /><br>
        
        {#if showChoiceSelector && currentStep === 1}
            {#key currentStep}
                <p class="choice-list">
                    <ChoiceSelector 
                        choices={['About me', 'View on GitHub', 'Back to Directory Listing']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(1, index)}
                    />
                </p>
            {/key}
        {/if}

    {:else if currentStep === 2}
        <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="I'm a 3rd year Computer Science student at Laurentian University." />
        <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="I have a passion for exploring AI technology, creative coding, and music creation." />
        <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="When I'm not immersed in tech, I enjoy gaming, writing, and spending quality time with my family." />
        <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="If you'd like, you can view my GitHub profile, check out my LinkedIn page, learn more about the project, or return to the main directory listing." on:animationComplete={() => handleAnimationComplete(2)} />
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>
        
        {#if showChoiceSelector && currentStep === 2}
            {#key currentStep}
                <p class="choice-list">
                    <ChoiceSelector 
                        choices={['View my GitHub', 'View my LinkedIn', 'About the project', 'Back to Directory Listing']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(2, index)}
                    />
                </p>
            {/key}
        {/if}
    {/if}
</section>
{/if}