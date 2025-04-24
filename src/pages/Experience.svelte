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
    <TextScroll startDelay={500} audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="Below lists all my professional work experience, sorted by recency."/>
    <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="2024 - DataLemur: Designed and launched a web-based SQL game using HTML, CSS, JS, and PGLite. The game gained 100,000+ visits and helped boost overall site traffic past 1,000,000+ visits. Enhanced engagement and brand visibility through technical, UI, and UX contributions."/><br>
    <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="2024 - Outlier/DataAnnotation: Worked with Outlier and DataAnnotation to improve LLM accuracy through data labeling, prompt validation, and comparative model evaluation. Analyzed and refined AI-generated outputs for clarity, factual consistency, and human-likeness, helping train safer and more useful language models."/><br>
    <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="2023 - Galatea Solutions: Led full-stack web development, delivering 15+ features ahead of schedule. Optimized performance to reduce load time by 50% and increase user engagement by 30%. Collaborated with a 3-person intern team to resolve 20+ bugs and improve user experience."/><br>
    <TextScroll startDelay={100} audioPlay={$audioEnabled} typingSpeed={50 / Number($textSpeed)} text="2022 - Entrepreneur: Launched and managed two online ventures combining e-commerce and educational content. Built a dropshipping business (LEDecor) selling LED art, overseeing product sourcing, 3PL logistics, MOQ negotiation, and managing suppliers. Later authored a neuroscience-based health book  (Insights of Wisdom) backed by 100+ studies and drove organic growth through SEO-optimized blogs, YouTube, TikTok, and persuasive copywriting. Gained hands-on experience in full-stack digital marketing, self-publishing, and product-market fit strategy." on:animationComplete={() => { 
        if (choiceList) {
            choiceList.style.visibility = 'visible';
        }
    }}/>
    <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

    <p class="choice-list" bind:this={choiceList} style="visibility: hidden;">
        <ChoiceSelector 
            choices={['Back']}
            isActive={choiceList?.style.visibility === 'visible'}
            onSelect={(index) => {
                clearTerminal();
                if (index === 0) navigateTo('navigation');
            }}
        />
    </p>
</section>
{/if}
