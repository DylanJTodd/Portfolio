<!-- src/lib/components/textflicker.svelte -->
<div class="text-flicker">
    <slot></slot>
</div>

<style>
    .text-flicker {
        --flicker-color: color-mix(in srgb, currentColor 20%, transparent);
        --base-offset: 3px;
        position: relative;
        display: inline-block;
        text-shadow: var(--base-offset) 0 var(--flicker-color);
        animation: flicker 0.2s infinite steps(1);
    }

    @keyframes flicker {
        0%, 50%, 100% {
            text-shadow: var(--base-offset) 0 var(--flicker-color);
        }
        25% {
            text-shadow: calc(var(--base-offset) * 1.5) 0 var(--flicker-color);
        }
        75% {
            text-shadow: calc(var(--base-offset) * 0.75) 0 var(--flicker-color);
        }
        85% {
            text-shadow: calc(var(--base-offset) * 1.25) 0 var(--flicker-color);
        }
    }
</style>