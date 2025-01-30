<script lang="ts">
    import TextScroll from '$lib/components/textscroll.svelte';
    import ChoiceSelector from '$lib/components/choiceselector.svelte';
    import ColorSelector from '$lib/components/colorselector.svelte';
    import ColorFilter from '$lib/components/colorfilter.svelte';

    import { goto } from '$app/navigation';
    import { audioEnabled, terminalColor } from '$lib/stores';
    import { fly } from 'svelte/transition';

    let terminalSection: HTMLElement;
    let choiceList: HTMLParagraphElement;
    let showContent = true;
    let currentStep = 1;
</script>
<ColorFilter>
    {#if showContent}
    <section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
        <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Welcome to my portfolio website!"/><br><br>
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50} text="This is a main directory listing. Feel free to navigate to any of the below pages." on:animationComplete={() => { 
            if (choiceList) {
                choiceList.style.visibility = 'visible';
            }
        }}/>
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

        <p class="choice-list" bind:this={choiceList}>
            <ChoiceSelector 
                choices={['Projects', 'About me']}
                onSelect={(index) => {
                    if (index === 0) goto('/projects');
                    if (index === 1) goto('about');
                }}
            />
        </p>

    </section>
    {/if}
</ColorFilter>