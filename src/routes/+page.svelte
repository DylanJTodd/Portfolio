<!-- src/routes/+page.svelte -->
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

    function clearTerminal() {
        showContent = false;
        setTimeout(() => {
            currentStep += 1;
            showContent = true;
        }, 1000);
    }
</script>

<ColorFilter>
    {#if showContent}
    <section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
        {#if currentStep === 1}
            <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="*************** PORTFOLIO-OS(R) V1.0.0 ***************"/><br><br>
            <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Setting up configuration..."/>
            <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..."/>
            <TextScroll startDelay={700} audioPlay={$audioEnabled} typingSpeed={75} text="..."/><br>
            <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Enable Audio?" on:animationComplete={() => { 
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
                        clearTerminal();
                    }}
                />
            </p>

        {:else if currentStep === 2}
            <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..."/>
            <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="..."/><br>
            <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={75} text={$audioEnabled ? "Audio Enabled" : "Audio Disabled"}/>
            <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Terminal Color Configuration" hideCaretManually={true}/><br>
            <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={50} text="Enter a color:" hideCaretManually={true} on:animationComplete={() => {
                if (choiceList) {
                    choiceList.style.visibility = 'visible';
                }
            }}/>

            <p class="choice-list" bind:this={choiceList}>
                <ColorSelector 
                    onColorSelect={(color) => {
                        $terminalColor = color;
                    }}
                    onContinue={() => {
                        clearTerminal();
                    }}
                />
            </p>

        {:else if currentStep === 3}
            <TextScroll audioPlay={$audioEnabled} typingSpeed={75} text={"Colors configured."}/>
            <TextScroll startDelay={400} audioPlay={$audioEnabled} typingSpeed={75} text="..."/>
            <TextScroll startDelay={800} audioPlay={$audioEnabled} typingSpeed={100} text="... OK"/><br><br>
            <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={50} text="Configuration complete..."/> <br>
            <TextScroll startDelay={1000} audioPlay={$audioEnabled} typingSpeed={75} text="Log In" on:animationComplete={() => { 
                if (choiceList) {
                    choiceList.style.visibility = 'visible';
                }
            }}/>

            <TextScroll hideCaretManually={true} startDelay={500} audioPlay={$audioEnabled} /><br>

            <p class="choice-list" bind:this={choiceList}>
                <ChoiceSelector 
                    choices={['Log In', 'Use Guest Mode']}
                    onSelect={(index) => {
                        goto('/navigation');
                    }}
                />
            </p>
        {/if}
    </section>
    {/if}
</ColorFilter>