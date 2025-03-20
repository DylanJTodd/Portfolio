<script lang="ts">
    import { onMount } from 'svelte';
    
    export let choices: string[] = [];
    export let onSelect: (index: number) => void;
    export let isActive: boolean = false; // New prop to control listener
    
    let currentIndex = 0;
    let keydownListener: ((event: KeyboardEvent) => void) | null = null;
    
    function handleKeydown(event: KeyboardEvent) {
        if (!isActive) return;
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
                isActive = false;
                break;
        }
    }
    
    function handleClick(index: number) {
        if (!isActive) return;
        currentIndex = index;
        onSelect(index);
    }
    
    function handleHover(index: number) {
        if (!isActive) return;
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
        on:click={() => handleClick(i)}
        on:mouseenter={() => handleHover(i)}
    >
        [{choice}]
    </button>
    {#if i < choices.length - 1}<br>{/if}
    {/each}