<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { onMount } from 'svelte';

    import { audioEnabled, isLoggedIn, lastLogin, textSpeed, userID } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let choiceList: HTMLParagraphElement;
    let showContent = true;
    let isAdmin = false;

    onMount(() => {
        if ($isLoggedIn) {
            fetch(`/api/users/${$userID}`)
                .then(res => res.json())
                .then(user => {
                    isAdmin = user.is_admin;
                })
                .catch(error => {
                    console.error('Error checking admin status:', error);
                });
        }
    });

    function clearTerminal() {
        showContent = false;
        setTimeout(() => {
            if (terminalSection) {
                terminalSection.innerHTML = '';
            }
            showContent = true;
        }, 1000);
    }

    $: choices = [
        'Projects', 
        'About', 
        'Contact Me', 
        $isLoggedIn ? 'Notes' : 'Must be logged in to view', 
        $isLoggedIn ? 'Settings' : 'Must be logged in to view',
        ...(isAdmin ? ['Message Manager'] : [])
    ];
    
    $: disabledChoices = !$isLoggedIn ? [3, 4] : [];
</script>
{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
    <TextScroll audioPlay={$audioEnabled} typingSpeed={50 * Number($textSpeed)} text="Welcome to my portfolio website!"/>
    {#if $lastLogin}
        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50 * Number($textSpeed)} text="Your last login was on {$lastLogin} (Server time)."/>
    {/if}
    <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={50 * Number($textSpeed)} text="This is a main directory listing. Feel free to navigate to any of the below pages." on:animationComplete={() => { 
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
                if (index === 2) navigateTo('contact');
                if (index === 3) navigateTo('notes');
                if (index === 4) navigateTo('settings');
                if (index === 5 && isAdmin) navigateTo('messagemanager');
            }}
        />
    </p>
</section>
{/if}