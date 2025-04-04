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

    let currentImageIndex = 0;
    const imagePaths = ['/res/squid1.jpg', '/res/squid2.jpg', '/res/squid3.jpg'];
    let currentImageSrc = imagePaths[0];
    let imageOpacity = 1;
    let intervalId: ReturnType<typeof setInterval> | undefined;

    onMount(() => {
        intervalId = setInterval(() => {
            fadeToNextImage();
        }, 5000);
    });

    function fadeToNextImage() {
        imageOpacity = 0;
        setTimeout(() => {
            currentImageIndex = (currentImageIndex + 1) % imagePaths.length;
            currentImageSrc = imagePaths[currentImageIndex];
            setTimeout(() => {
                imageOpacity = 1;
            }, 50);
        }, 300);
    }

    onDestroy(() => {
        if (intervalId) {
            clearInterval(intervalId);
        }
    });

    function clearTerminal(page: number) {
        inputEnabled = false;
        showContent = false;
        setTimeout(() => {
            if (terminalSection) {
                terminalSection.innerHTML = '';
            }
            currentStep = page;
            showContent = true;
        }, 1000);
    }
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
    <img src={currentImageSrc} alt="SQL Squid Games Home Page" class="project-image" style="opacity: {imageOpacity};"/>

    {#key currentStep}
        {#if currentStep === 1}
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="SQL Squid Games was a website I developed when I worked at DataLemur "/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="Although I cannot publicly provide the code, I am more than happy to explain what I did below."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="First I Conducted user and competitor research (playing all other sql games), compiling key insights. Then I binged all of squid games over a weekend, and presented my findings to the CEO. Then I Designed Figma prototypes, iterated, and finalized the site layout."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="For the front-end, specifically only html and css were used, along with javascript. There was no backend, as we wanted this to be a light website, We conducted queries through PGLite, (Postgres)."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 * Number($textSpeed)} text="I came up with all the scenarios, schemas, and queries myself, and it overall made for a really fun game! There is now over 500,000 visits to the site, and it was received well."/>
            <TextScroll startDelay={500} hideCaretManually={true} audioPlay={false} typingSpeed={50 * Number($textSpeed)} text="" on:animationComplete={() => handleAnimationComplete(1)} />

            <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
                <ChoiceSelector
                    choices={['Visit the site', 'Discover other projects']}
                    isActive={inputEnabled}
                    onSelect={(index) => {
                        inputEnabled = false;
                        if (index === 0) { window.open('https://datalemur.com/sql-game', '_blank'); inputEnabled = true; }
                        if (index === 1) navigateTo('projects');
                    }}
                />
            </p>
        {/if}
    {/key}
</section>
{/if}