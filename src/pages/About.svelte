<script lang="ts">
    import { onDestroy, tick, onMount } from 'svelte';
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
                    break;
                case 2:
                    navigateTo('navigation');
                    break;
                default:
                    inputEnabled = true;
                    break;
            }
        } else if (currentStep === 2) {
            switch (choiceIndex) {
                case 0:
                     window.open('https://github.com/DylanJTodd', '_blank');
                     inputEnabled = true;
                     break;
                case 1:
                    window.open('https://www.linkedin.com/in/dylan-j-todd/', '_blank');
                    inputEnabled = true;
                    break;
                case 2:
                    requestTransition(1);
                    break;
                case 3:
                     navigateTo('navigation');
                     break;
                default:
                     inputEnabled = true;
                     break;
            }
        }
    }

    async function handleAnimationComplete(step: number) {
        if (transitionInProgress || step !== currentStep) return;

        if ([1, 2].includes(step)) {
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
        }
    }

</script>

{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
    {#key currentStep}
        {#if currentStep === 1}
            <TextScroll audioPlay={$audioEnabled} typingSpeed={30 / Number($textSpeed)} text="Hello! My name is Dylan Todd. Thank you for viewing my portfolio website." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 / Number($textSpeed)} text="This website was created for a web design class in my 3rd year of university, inspired by the Fallout 4 terminal." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 / Number($textSpeed)} text="It uses the LAMP stack—Linux, Apache, MySQL, and PHP with Apache hosted via HTTPD and MySQL managed through MariaDB. The server is hosted on a free instance of oracle." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 / Number($textSpeed)} text="My frontend was built using Svelte, with TypeScript. My backend is communicated to via a RESTful API, that securely manages all communications." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={30 / Number($textSpeed)} text="You can find all of the code (aside from the sensitive backend) via GitHub" />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(1)} />

            {#if showChoiceSelector && currentStep === 1}
                <p class="choice-list">
                    <ChoiceSelector
                        choices={['About me', 'View on GitHub', 'Back to Directory Listing']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(1, index)}
                    />
                </p>
            {/if}

        {:else if currentStep === 2}
            <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="I'm a 3rd year Computer Science student at Laurentian University." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="I have a passion for exploring AI technology, creative coding, and music creation." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="When I'm not immersed in tech, I enjoy gaming, writing, and spending quality time with my family." />
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="If you'd like, you can view my GitHub profile, check out my LinkedIn page, learn more about the project, or return to the main directory listing." />
            <TextScroll hideCaretManually={true} startDelay={1} audioPlay={false} text="" on:animationComplete={() => handleAnimationComplete(2)} />

            {#if showChoiceSelector && currentStep === 2}
                <p class="choice-list">
                    <ChoiceSelector
                        choices={['View my GitHub', 'View my LinkedIn', 'About the project', 'Back to Directory Listing']}
                        isActive={inputEnabled && !transitionInProgress}
                        onSelect={(index) => handleSelection(2, index)}
                    />
                </p>
            {/if}
        {/if}
    {/key}
</section>
{/if}