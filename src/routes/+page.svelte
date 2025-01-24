<script lang="ts">
    import TextScroll from '$lib/components/textscroll.svelte';
    import ChoiceSelector from '$lib/components/choiceselector.svelte';
    let choiceList: HTMLParagraphElement;
    let showChoices = false;
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
            choices={['Enable Audio', 'Disable Audio', 'Exit']}
            onSelect={(index) => console.log(`Selected ${index + 1}`)}
        />
    </p>
</section>

<style>
    .choice-list {
        visibility: hidden;
    }
</style>