<script lang="ts">
    import { onMount } from 'svelte';
    
    export let choices: string[] = [];
    export let onSelect: (index: number) => void;
    export let isActive: boolean = false;
    export let disabledChoices: number[] = [];
    export let isTyping: boolean = false;
    
    let currentIndex = 0;
    let keydownListener: ((event: KeyboardEvent) => void) | null = null;
    
    function handleKeydown(event: KeyboardEvent) {
        if (!isActive || isTyping) return;
        switch (event.key) {
            case 'ArrowUp':
                event.preventDefault();
                currentIndex = (currentIndex - 1 + choices.length) % choices.length;
                break;
            case 'ArrowDown':
                event.preventDefault();
                currentIndex = (currentIndex + 1) % choices.length;
                break;
            case 'Enter':
                event.preventDefault();
                if (!disabledChoices.includes(currentIndex)) {
                    onSelect(currentIndex);
                    isActive = false;
                }
                break;
        }
    }
    
    function handleClick(index: number) {
        if (!isActive || disabledChoices.includes(index)) return;
        currentIndex = index;
        onSelect(index);
    }
    
    function handleHover(index: number) {
        if (!isActive || disabledChoices.includes(index)) return;
        currentIndex = index;
    }
    
    onMount(() => {
        keydownListener = handleKeydown;
        window.addEventListener('keydown', keydownListener);
        return () => {
            if (keydownListener) {
                window.removeEventListener('keydown', keydownListener);
            }
        };
    });
</script>
    
{#each choices as choice, i}
<button
    class="terminal-choice"
    class:choice-selected={i === currentIndex}
    class:is-disabled={disabledChoices.includes(i)}
    on:click={() => handleClick(i)}
    on:mouseenter={() => handleHover(i)}
    disabled={disabledChoices.includes(i)}
>
    [{choice}]
</button>
{#if i < choices.length - 1}<br>{/if}
{/each}

<style>
    .terminal-choice.is-disabled {
        color: gray;
        cursor: not-allowed;
    }
</style>