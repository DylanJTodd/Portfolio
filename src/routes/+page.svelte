<script lang="ts">
    import TextScroll from '$lib/components/textscroll.svelte';
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
        <button class="terminal-choice choice-selected">[Enable Audio]</button><br>
        <button class="terminal-choice">[Disable Audio]</button>
    </p>
</section>

