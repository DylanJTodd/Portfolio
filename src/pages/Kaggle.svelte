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

    {#key currentStep}
        {#if currentStep === 1}
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="This page contains information on various mini-projects / exercises that I've done on Kaggle. Some are bigger than others, some are more complex."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="First and most impressive was the ISIC 2024 skin cancer competition. The point of this competition was given images on skin lesions, whether you could create an AI that determined if it was skin cancer or not. There were many hardships and different obstacles to overcome, but I utilized an ensembled CNN to perform the binary classification. It was ensembled with different methods to reduce the data skew since only ~2% of the data was cancer"/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="Another mini project I did was creating a FFN (simple SLP) from scratch using nothing but math. I didn't use any libraries. This was done at the beginning of my AI journey so I could truly understand the underlying math and principles behind modern Deep Learning."/>
            <TextScroll startDelay={500} hideCaretManually={true} audioPlay={false} typingSpeed={50 / Number($textSpeed)} text="" on:animationComplete={() => handleAnimationComplete(1)} />

            <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
                <ChoiceSelector
                    choices={['Visit my Kaggle page', 'Discover other projects']}
                    isActive={inputEnabled}
                    onSelect={(index) => {
                        inputEnabled = false;
                        if (index === 0) { window.open('https://www.kaggle.com/dylanjtodd', '_blank'); inputEnabled = true; }
                        if (index === 1) navigateTo('projects');
                    }}
                />
            </p>
        {/if}
    {/key}
</section>
{/if}