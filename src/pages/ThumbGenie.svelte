<script lang="ts">
    import { tick, onMount, onDestroy } from 'svelte';
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';

    import { audioEnabled, textSpeed } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let choiceList: HTMLElement | null = null;
    let showContent = true;
    let currentStep = 1;
    let inputEnabled = false;

    async function handleAnimationComplete(step: number) {
        if (step !== currentStep) return;

        if ([1, 2, 3].includes(step)) {
             await tick();
             if (currentStep === step) {
                if (choiceList) {
                    choiceList.style.visibility = 'visible';
                }
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
    <img src='/res/thumbgenie.png' alt="ThumbGenie demonstration" style="margin-bottom: 0.5rem; margin-top: 2em; margin-left: 1rem; float: right; height: 65vh;"/>

    {#key currentStep}
        {#if currentStep === 1}
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="ThumbGenie was my first Deep Learning project, where I took a kaggle dataset of popular youtube thumbnails (with metadata), and trained an image generator (first GAN, then diffuser) to generate proper thumbnails."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="Unfortunately, because of physical and financial limitations, the training for this project was very subpar, and use of it requires the user to train their it themselves before using it. This was my first, and my last image generation project because of this."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="Feel free to check out the github for this project!"/>
            <TextScroll startDelay={500} hideCaretManually={true} audioPlay={false} typingSpeed={50 / Number($textSpeed)} text="" on:animationComplete={() => handleAnimationComplete(1)} />

            <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
                <ChoiceSelector
                    choices={['View the Code', 'Discover other projects']}
                    isActive={inputEnabled}
                    onSelect={(index) => {
                        inputEnabled = false;
                        if (index === 0) { window.open('https://github.com/DylanJTodd/ThumbGenie', '_blank'); inputEnabled = true; }
                        if (index === 1) navigateTo('projects');
                    }}
                />
            </p>
        {/if}
    {/key}
</section>
{/if}