<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';

    import { audioEnabled, isLoggedIn, lastLogin } from '../stores/globalStore';
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

    const choices = ['Projects', 'About', $isLoggedIn ? 'Notes' : 'Must be logged in to view', $isLoggedIn ? 'Settings' : 'Must be logged in to view'];
    const disabledChoices = !$isLoggedIn ? [2,3] : [];
</script>
{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
    <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Welcome to my portfolio website!"/>
    {#if $lastLogin}
        <TextScroll startDelay={2000} audioPlay={$audioEnabled} typingSpeed={50} text="Your last login was on {$lastLogin}."/>
    {/if}
    <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50} text="This is a main directory listing. Feel free to navigate to any of the below pages." on:animationComplete={() => { 
        if (choiceList) {
            choiceList.style.visibility = 'visible';
        }
    }}/>
    <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} />

    <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
        <ChoiceSelector 
            choices={choices}
            disabledChoices={disabledChoices}
            isActive={choiceList?.style.visibility === 'visible'}
            onSelect={(index) => {
                clearTerminal();
                if (index === 0) navigateTo('projects');
                if (index === 1) navigateTo('about');
                if (index === 2) navigateTo('notes');
                if (index === 3) navigateTo('settings');
            }}
        />
    </p>
</section>
{/if}