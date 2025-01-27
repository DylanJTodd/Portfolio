<!-- Configuration Page -->

<script lang="ts">
    import TextScroll from '$lib/components/textscroll.svelte';
    import ChoiceSelector from '$lib/components/choiceselector.svelte';
	import { audioEnabled } from '$lib/stores';
    let choiceList: HTMLParagraphElement;

</script>

<section class="terminal-opening">
    <TextScroll audioPlay={false} typingSpeed={50} text="************* Portfolio-OS(R) V1.0.0 ************"/><br><br>
    <TextScroll startDelay={1000} audioPlay={false} typingSpeed={75} text="Setting up configuration..."/>
    <TextScroll startDelay={400} audioPlay={false} typingSpeed={75} text="..."/>
    <TextScroll startDelay={700} audioPlay={false} typingSpeed={75} text="..."/><br>
    <TextScroll startDelay={1000} audioPlay={false} typingSpeed={75} text="Enable Audio?" on:animationComplete={() => { 
        if (choiceList) {
            choiceList.style.visibility = 'visible';
        }
    }}/>
    <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

    <p class="choice-list" bind:this={choiceList}>
        <ChoiceSelector 
            choices={['Enable Audio', 'Disable Audio']}
            onSelect={(index) => {
                if (index === 0) $audioEnabled = true;
                if (index === 1) $audioEnabled = false;
            }}
        />
    </p>
    
</section>

<style>
    .choice-list {
        visibility: hidden;
    }
</style>