<!-- src/lib/components/textglow.svelte -->
<div class="text-glow">
    <slot></slot>
</div>

<style>
    .text-glow {
        --glow-color: #ffffff;
        --glow-spread: 8px;
        --glow-opacity: 0.1;
        position: relative;
        display: inline-block;
        color: var(--glow-color);
        text-shadow: 
            0 0 var(--glow-spread) var(--glow-color),
            0 0 calc(var(--glow-spread) * 2) rgba(43, 255, 0, var(--glow-opacity)),
            0 0 calc(var(--glow-spread) * 3) rgba(43, 255, 0, calc(var(--glow-opacity) * 0.5));
        animation: pulse 4s infinite;
        pointer-events: auto; /* Ensure it allows interactions */
    }

    @keyframes pulse {
        0%, 100% {
            text-shadow: 
                0 0 var(--glow-spread) var(--glow-color),
                0 0 calc(var(--glow-spread) * 2) rgba(43, 255, 0, var(--glow-opacity)),
                0 0 calc(var(--glow-spread) * 3) rgba(43, 255, 0, calc(var(--glow-opacity) * 0.5));
        }
        50% {
            text-shadow: 
                0 0 calc(var(--glow-spread) * 1.5) var(--glow-color),
                0 0 calc(var(--glow-spread) * 2.5) rgba(43, 255, 0, var(--glow-opacity)),
                0 0 calc(var(--glow-spread) * 3.5) rgba(43, 255, 0, calc(var(--glow-opacity) * 0.5));
        }
    }
</style>