<!-- src/lib/components/ColorSelector.svelte -->
<script lang="ts">
    import { onMount } from 'svelte';
    import { audioEnabled, terminalColor } from '../stores/globalStore';

    export let onColorSelect: (color: string) => void;
    export let onContinue: () => void;
    export let isActive: boolean;

    interface ColorOption {
        name: string;
        value: string;
    }

    const colors: ColorOption[] = [
        { name: 'Green', value: '#00ff00' },
        { name: 'Pink', value: '#ff69b4' },
        { name: 'Yellow', value: '#ffff00' },
        { name: 'Orange', value: '#ffa500' },
        { name: 'Red', value: '#ff0000' },
        { name: 'Magenta', value: '#ff00ff' },
        { name: 'Blue', value: '#0000ff' },
        { name: 'Cyan', value: '#00ffff' },
        { name: 'White', value: '#ffffff' },
        { name: 'Custom Hex', value: 'hex' }
    ];

    let currentIndex = 0;
    let showHexInput = false;
    let hexValue = '';
    let isHexMode = false;
    let hexInputElement: HTMLInputElement;
    let customHexColor: string | null = null;

    $: currentColor = $terminalColor;
    $: if (!isHexMode && showHexInput) {
        showHexInput = false;
        hexValue = '';
    }

    function isValidHex(hex: string): boolean 
    {
        const cleanHex = hex.replace('#', '');
        return /^[0-9A-Fa-f]{6}$/.test(cleanHex);
    }

    function handleKeydown(event: KeyboardEvent) {
        if (!isActive) return;
        if (isHexMode) {
            if (event.key === 'Escape') {
                exitHexMode();
                return;
            }
            return;
        }
        switch (event.key) {
            case 'ArrowUp':
                event.preventDefault();
                currentIndex = Math.max(0, currentIndex - 2);
                break;
            case 'ArrowDown':
                event.preventDefault();
                if (currentIndex < colors.length - 2) {
                    currentIndex += 2;
                } else {
                    currentIndex = colors.length;
                }
                break;
            case 'ArrowLeft':
                event.preventDefault();
                if (currentIndex % 2 === 1) currentIndex--;
                break;
            case 'ArrowRight':
                event.preventDefault();
                if (currentIndex % 2 === 0 && currentIndex < colors.length - 1) currentIndex++;
                break;
            case 'Enter':
            case ' ':
                event.preventDefault();
                if (currentIndex === colors.length) {
                    onContinue();
                } else {
                    handleSelection(currentIndex);
                }
                break;
        }
    }

    function handleSelection(index: number) {
        const selection = colors[index];
        if (selection.value === 'hex') {
            enterHexMode();
        } else {
            customHexColor = null;
            onColorSelect(selection.value);
            $terminalColor = selection.value;
        }
    }

    function handleHexSubmit() 
    {
    const cleanHex = hexValue.replace('#', '');
        if (isValidHex(cleanHex)) 
        {
            const processedHex = '#' + cleanHex;
            customHexColor = processedHex;
            onColorSelect(processedHex);
            $terminalColor = processedHex;
            exitHexMode();
        }
    }   

    function enterHexMode() {
        isHexMode = true;
        showHexInput = true;
        window.removeEventListener('keydown', handleKeydown);
        setTimeout(() => {
            if (hexInputElement) {
                hexInputElement.focus();
            }
        }, 0);
    }

    function exitHexMode() {
        isHexMode = false;
        showHexInput = false;
        hexValue = '';
        window.addEventListener('keydown', handleKeydown);
    }

    function handleHover(index: number) {
        if (!isHexMode) {
            currentIndex = index;
        }
    }

    onMount(() => {
        window.addEventListener('keydown', handleKeydown);
        return () => window.removeEventListener('keydown', handleKeydown);
    });
</script>

<div class="color-selector" role="listbox" aria-label="Color selection">
    <div class="color-grid">
        {#each colors as color, i}
            {#if i % 2 === 0}
                <div class="color-row">
                    <button
                        type="button"
                        class="color-option"
                        class:selected={currentIndex === i && !isHexMode}
                        on:mouseenter={() => handleHover(i)}
                        on:click={() => handleSelection(i)}
                        on:keydown={(e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                handleSelection(i);
                            }
                        }}
                        role="option"
                        aria-selected={currentIndex === i && !isHexMode}
                    >
                        <div class="color-option-container">
                            <div class="color-row-content">
                                <span 
                                    class="color-box" 
                                    style="background-color: {color.value === 'hex' ? '#ffffff' : color.value}"
                                    aria-hidden="true"
                                ></span>
                                {color.name} {color.value === currentColor || (color.value === 'hex' && customHexColor === currentColor) ? '(Current)' : ''}
                            </div>
                        </div>
                    </button>
                    
                    {#if i + 1 < colors.length}
                        <button
                            type="button"
                            class="color-option"
                            class:selected={currentIndex === i + 1 && !isHexMode}
                            on:mouseenter={() => handleHover(i + 1)}
                            on:click={() => handleSelection(i + 1)}
                            on:keydown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    e.preventDefault();
                                    handleSelection(i + 1);
                                }
                            }}
                            role="option"
                            aria-selected={currentIndex === i + 1 && !isHexMode}
                        >
                            <div class="color-option-container">
                                <div class="color-row-content">
                                    <span 
                                        class="color-box" 
                                        style="background-color: {colors[i + 1].value === 'hex' ? '#ffffff' : colors[i + 1].value}"
                                        aria-hidden="true"
                                    ></span>
                                    {colors[i + 1].name} {colors[i + 1].value === currentColor || (colors[i + 1].value === 'hex' && customHexColor === currentColor) ? '(Current)' : ''}
                                </div>
                                {#if colors[i + 1].value === 'hex' && showHexInput}
                                    <div class="hex-input">
                                        <input
                                            type="text"
                                            bind:this={hexInputElement}
                                            bind:value={hexValue}
                                            placeholder="#000000"
                                            on:keydown={(e) => {
                                                e.stopPropagation();
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    handleHexSubmit();
                                                }
                                                if (e.key === 'Escape') {
                                                    e.preventDefault();
                                                    exitHexMode();
                                                }
                                            }}
                                            on:blur={() => {
                                                if (isValidHex(hexValue)) {
                                                    handleHexSubmit();
                                                }
                                            }}
                                            aria-label="Enter hex color value"
                                        />
                                    </div>
                                {/if}
                            </div>
                        </button>
                    {/if}
                </div>
            {/if}
        {/each}
    </div>

    <button
        type="button"
        class="continue-option"
        class:selected={currentIndex === colors.length}
        on:mouseenter={() => handleHover(colors.length)}
        on:click={onContinue}
        on:keydown={(e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                onContinue();
            }
        }}
    >
        Continue
    </button>
</div>

<style>
    .color-selector {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .color-grid {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .color-row {
        display: flex;
        gap: 2rem;
    }

    button {
        outline: none !important;
    }

    button:focus {
        outline: none;
    }

    .color-option-container {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .color-row-content {
        display: flex;
        align-items: center;
    }

    .color-box {
        width: 1rem;
        height: 1rem;
        border: 1px solid #ffffff;
        margin-right: 0.5rem;
    }

    .color-option, .continue-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        padding: 0.25rem;
        background: none;
        border: none;
        color: inherit;
        font: inherit;
        text-align: left;
        width: 100%;
        transition: background-color 0.1s ease;
    }

    .selected {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .color-option:hover, .continue-option:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .continue-option.selected {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .hex-input {
        margin-top: 0.5rem;
        margin-left: 1.5rem;
    }

    .hex-input input {
        background-color: transparent;
        border: 1px solid #ffffff;
        color: #ffffff;
        padding: 0.25rem;
        width: 7rem;
    }
</style>