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
        <TextScroll startDelay={5000} audioPlay={$audioEnabled} typingSpeed={50} text="Hello! My name is Dylan Todd. Thank you for viewing my portfolio website. First I'm going to describe this website a little bit, and then talk a bit about myself. <br>
                                                                                       So to start, this website was created for a web design class in my 3rd year of university. It uses the WAMP stack, namely Windows, Apache, MYSQL, and PHP. <br>
                                                                                       Apache is hosted with XAMPP, MySQL through phpMyAdmin, and PHP using PDO.<br>
                                                                                       My front end was done using Sveltekit, and I used typescript as well instead of javascript. Originally, I planed on using TailwindCSS but due to course requirements this wasn't feasible. <br>" on:animationComplete={() => { 
            if (choiceList) {
                choiceList.style.visibility = 'visible';
            }
        }}/>
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

        <p class="choice-list" bind:this={choiceList}>
            <ChoiceSelector 
                choices={['View on Github','Back to Directory Listing']}
                onSelect={(index) => {
                    if (index === 0) goto('/navigation') //Open Github instead
                    if (index === 1) goto('/navigation');
                }}
            />
        </p>

    </section>
    {/if}
</ColorFilter>