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
    let currentStep = 1;

    function clearTerminal(page: number) {
        showContent = false;
        setTimeout(() => {
            if (page == 1){
                currentStep = 1
            }
            if (page == 2){
                currentStep = 2
            }
            showContent = true;
        }, 1000);
    }
</script>
<ColorFilter>
    {#if showContent}
    <section class="terminal-opening" bind:this={terminalSection} in:fly="{{ y: 0, duration: 1000 }}" out:fly="{{ y: -1000, duration: 1000 }}">
        {#if currentStep === 1}
            <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={40} text="Hello! My name is Dylan Todd. Thank you for viewing my portfolio website. First I'm going to describe this website a little bit, and then talk about myself."/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="So to start, this website was created for a web design class in my 3rd year of university, based on the Fallout 4 terminal. It uses the LAMP stack, namely Linux, Apache, MYSQL, and PHP."/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="Apache is hosted with XAMPP, MySQL through phpMyAdmin, and PHP using PDO. The domain was gotten through namecheap, and the server is hosted via the free oracle tier."/> <br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="My front end was done using Sveltekit, and I used typescript as well instead of javascript. Originally, I planned on using TailwindCSS but due to course requirements this wasn't feasible."/> <br> 

            <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={1} text="Hello! My name is Dylan Todd. Thank you for viewing my portfolio website. First I'm going to describe this website a little bit, and then talk about myself."/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={1} text="So to start, this website was created for a web design class in my 3rd year of university, based on the Fallout 4 terminal. It uses the LAMP stack, namely Linux, Apache, MYSQL, and PHP."/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={1} text="Apache is hosted with XAMPP, MySQL through phpMyAdmin, and PHP using PDO. The domain was gotten through namecheap, and the server is hosted via the free oracle tier."/> <br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={1} text="My front end was done using Sveltekit, and I used typescript as well instead of javascript. Originally, I planned on using TailwindCSS but due to course requirements this wasn't feasible."/> <br> 


            <!-- Insert short description of back end technologies -->

            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="If you'd like, you can view the code on github, learn a little more about me, or go back to the main directory listing." on:animationComplete={() => {
                if (choiceList) {
                    choiceList.style.visibility = 'visible';
                }
            }}/>
            <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

            <p class="choice-list" bind:this={choiceList}>
                <ChoiceSelector 
                    choices={['About me', 'View on Github','Back to Directory Listing']}
                    onSelect={(index) => {
                        if (index === 0) clearTerminal(2);
                        if (index === 1) window.open('https://github.com/DylanJTodd/Portfolio', '_blank');
                        if (index === 2) goto('/navigation');
                    }}
                />
            </p>
        {:else if currentStep === 2}
            <TextScroll startDelay={200} audioPlay={$audioEnabled} typingSpeed={40} text="Hello! I'm Dylan Todd, I'm currently a 3rd year student at Laurentian University, pursuing a bachelors degree in Computer Science."/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="I love spending my free time working with various AI technologies, and I've made some pretty cool projects so far!"/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="For personal hobbies, I used to spend a lot of time gaming, but have found myself navigating more towards music creation, writing, and spending more time with my family."/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="I'm always looking for new opportunities to learn and grow, and I'm excited for my future"/><br>
            <TextScroll startDelay={0} audioPlay={$audioEnabled} typingSpeed={40} text="If you'd like, you can view the code on GitHub, view my LinkedIn profile, go back to learning more about the project, or go back to the main directory listing." on:animationComplete={() => {
                if (choiceList) {
                    choiceList.style.visibility = 'visible';
                }

                
            }}/>

            <TextScroll hideCaretManually={true} startDelay={500} audioPlay={false} /><br>

            <p class="choice-list" bind:this={choiceList}>
                <ChoiceSelector 
                    choices={['View my Github','View my LinkedIn', 'About the project', 'Back to Directory Listing']}
                    onSelect={(index) => {
                        if (index === 0) window.open('https://github.com/DylanJTodd/Portfolio', '_blank');
                        if (index === 1) window.open('https://www.linkedin.com/in/dylan-j-todd/', '_blank');
                        if (index === 2) clearTerminal(1);
                        if (index === 3) goto('/navigation');
                    }}
                />
            </p>
        {/if}
    </section>
    {/if}
</ColorFilter>