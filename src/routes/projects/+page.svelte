<script lang="ts">
    import TextScroll from '$lib/components/textscroll.svelte';
    import ChoiceSelector from '$lib/components/choiceselector.svelte';
    import ColorFilter from '$lib/components/colorfilter.svelte';

    import { goto } from '$app/navigation';
    import { audioEnabled} from '$lib/stores';
    import { fly } from 'svelte/transition';

    let terminalSection: HTMLElement;
    let choiceList: HTMLParagraphElement;
    let showContent = true;
</script>
<ColorFilter>
    {#if showContent}
    <section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50} text="Please choose any project name that interests you, and learn what it's about!" on:animationComplete={() => { 
            if (choiceList) {
                choiceList.style.visibility = 'visible';
            }
        }}/>
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

        <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
            <ChoiceSelector 
                choices={['This Portfolio Site', 'Educational Web-based SQL Game']}
                isActive={choiceList?.style.visibility === 'visible'}
                onSelect={(index) => {
                    if (index === 0) goto('about');
                    if (index === 1) goto('projects/sql_squid_games');
                }}
            />
        </p>

    </section>
    {/if}
</ColorFilter>