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
    const imagePaths = ['/res/fb1.jpg', '/res/fb2.jpg', '/res/fb3.jpg'];
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
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="This Facebook redesign was done for a second year UI design class. This was actually my first time using html, js, and css!"/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="The whole point of the class was to take a site and improve it's interface. Me and my team chose Facebook, and I was able to lead and create this site. The focus was more on the UI and UX rather than actual technical functionality."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="The design started with user research, looking for pain points, other examples of good interfaces, then moved onto prototype drawings, first on paper, then on figma, and then finaly implementing it."/>
            <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40 / Number($textSpeed)} text="Feel free to view the github for this code, and the website itself."/>
            <TextScroll startDelay={500} hideCaretManually={true} audioPlay={false} typingSpeed={50 / Number($textSpeed)} text="" on:animationComplete={() => handleAnimationComplete(1)} />

            <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
                <ChoiceSelector
                    choices={['View the code', 'Visit the site', 'Discover other projects']}
                    isActive={inputEnabled}
                    onSelect={(index) => {
                        inputEnabled = false;
                        if (index === 0) { window.open('https://github.com/DylanJTodd/Facebook-UI-Redesign', '_blank'); inputEnabled = true; }
                        if (index === 1) { window.open('https://dylanjtodd.github.io/Facebook-UI-Redesign', '_blank'); inputEnabled = true; }
                        if (index === 2) navigateTo('projects');
                    }}
                />
            </p>
        {/if}
    {/key}
</section>
{/if}