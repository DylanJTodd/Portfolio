<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';

    import { audioEnabled, textSpeed } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let choiceList: HTMLParagraphElement;
    let showContent = true;
    

    function clearTerminal() {
        showContent = false;
        setTimeout(() => {
            if (terminalSection) {
                terminalSection.innerHTML = '';
            }
            showContent = true;
        }, 1000);
    }
</script>

{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
    <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="Please choose any project name that interests you, and learn what it's about!" on:animationComplete={() => { 
        if (choiceList) {
            choiceList.style.visibility = 'visible';
        }
    }}/>
    <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

    <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
        <ChoiceSelector 
            choices={['This Portfolio Site (About me)','Educational Web-based SQL Game', 'Kaggle Repository', 'Back']}
            isActive={choiceList?.style.visibility === 'visible'}
            onSelect={(index) => {
                clearTerminal();
                if (index === 0) navigateTo('about');
                if (index === 1) navigateTo('sql_squid_games');
                if (index === 2) navigateTo('kaggle');
                if (index === 3) navigateTo('navigation');
            }}
        />
    </p>
</section>
{/if}
