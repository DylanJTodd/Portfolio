<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';

    import { audioEnabled } from '../stores/globalStore';
    import { fly } from 'svelte/transition';
    import { navigateTo } from '../stores/routeStore';

    let terminalSection: HTMLElement;
    let choiceList: HTMLElement | null = null;
    let showContent = true;
    let currentStep = 1;

    function clearTerminal(page: number) {
        showContent = false;
        setTimeout(() => {
            if (terminalSection) {
                terminalSection.innerHTML = '';
            }
            currentStep = page;
            showContent = true;
        }, 1000);
    }
</script>

{#if showContent}
<section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
    <img src="/res/squid.jpg" alt="SQL Squid Games Home Page" style="float: right; margin-left: 1rem; width: 50vw;"/><br>

    {#if currentStep === 1} <!-- General Info -->
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="SQL Squid Games was a website I developed when I worked at DataLemur "/>
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="Although I cannot publicly provide the code, I am more than happy to explain what I did below."/>
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="The very first thing I did was user research. This meant scouring forums, reddit, social media, and watching both seasons over a weekend (crazy, right?)"/>
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="Then, the next thing I did was competitor research. I played every web-based SQL game that exists, compiled a list of the best and worst parts, some desirable features, and created a presentation and ran it by the CEO."/>
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="Then came the design. I made a few different Figma prototypes, got one approved, and got a general idea of what the site should look like and how it should operate. This was my starting point."/>

        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50} text="" on:animationComplete={() => { 
            if (choiceList) {
                choiceList.style.visibility = 'visible';
            }
        }}/>
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} />

        <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
            <ChoiceSelector 
                choices={['Learn about the frontend (UNDER CONSTRUCTION)', 'Learn about the backend (UNDER CONSTRUCTION)', 'Visit the site', 'Discover other projects']}
                isActive={choiceList?.style.visibility === 'visible'}
                onSelect={(index) => {
                    if (index === 0) console.log("Under Construction1"); // clearTerminal(2);
                    if (index === 1) console.log("Under Construction2"); // clearTerminal(3);
                    if (index === 2) window.open('https://datalemur.com/sql-game', '_blank');
                    if (index === 3) clearTerminal(currentStep); navigateTo('projects');
                }}
            />
        </p>
    {:else if currentStep === 2} <!-- Frontend Info -->
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="Testing Frontend"/><br>

        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50} text="" on:animationComplete={() => { 
            if (choiceList) {
                choiceList.style.visibility = 'visible';
            }
        }}/>
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

        <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
            <ChoiceSelector 
                choices={['Learn about the frontend', 'Learn about the backend', 'Visit the site', 'Discover other projects']}
                isActive={choiceList?.style.visibility === 'visible'}
                onSelect={(index) => {
                    if (index === 0) clearTerminal(2);
                    if (index === 1) clearTerminal(3);
                    if (index === 2) window.open('https://datalemur.com/sql-game', '_blank');
                    if (index === 3) clearTerminal(currentStep); navigateTo('projects');
                }}
            />
        </p>
    {:else if currentStep === 3} <!-- Backend Info -->
        <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={40} text="Testing Backend"/><br>

        <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={50} text="" on:animationComplete={() => { 
            if (choiceList) {
                choiceList.style.visibility = 'visible';
            }
        }}/>
        <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

        <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
            <ChoiceSelector 
                choices={['Learn about the frontend', 'Learn about the backend', 'Visit the site', 'Discover other projects']}
                isActive={choiceList?.style.visibility === 'visible'}
                onSelect={(index) => {
                    if (index === 0) clearTerminal(2);
                    if (index === 1) clearTerminal(3);
                    if (index === 2) window.open('https://datalemur.com/sql-game', '_blank');
                    if (index === 3) clearTerminal(currentStep); navigateTo('projects');
                }}
            />
        </p>
    {/if}
</section>
{/if}