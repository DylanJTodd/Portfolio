<!-- src/lib/components/choiceselector.svelte -->
<script lang="ts">
    import { onMount } from 'svelte';

    export let choices: string[] = [];
    export let onSelect: (index: number) => void;

    let currentIndex = 0;

    function handleKeydown(event: KeyboardEvent) {
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
                onSelect(currentIndex);
                break;
        }
    }

    function handleClick(index: number) {
        currentIndex = index;
        onSelect(index);
    }

    function handleHover(index: number) {
        currentIndex = index;
    }

    onMount(() => {
        window.addEventListener('keydown', handleKeydown);
        return () => window.removeEventListener('keydown', handleKeydown);
    });
</script>

{#each choices as choice, i}
    <button 
        class="terminal-choice"
        class:choice-selected={i === currentIndex}
        on:click={() => handleClick(i)}
        on:mouseenter={() => handleHover(i)}
    >
        [{choice}]
    </button>
    {#if i < choices.length - 1}<br>{/if}
{/each}