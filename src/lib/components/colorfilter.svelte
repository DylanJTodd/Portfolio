<!-- src/lib/components/ColorFilter.svelte -->
<script lang="ts">
    import { terminalColor } from '$lib/stores';
    export let opacity: number = 0.4;
</script>

<div class="color-filter">
    <slot></slot>
    <div class="overlay-color" style="--filter-color: {$terminalColor}; --filter-opacity: {opacity}"></div>
    <div class="overlay-desaturate"></div>
</div>

<style>
    .color-filter {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .overlay-color {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--filter-color);
        opacity: var(--filter-opacity);
        z-index: 4;
        mix-blend-mode: color;
        pointer-events: none;
    }

    .overlay-desaturate {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #808080;
        z-index: 3;
        mix-blend-mode: saturation;
        pointer-events: none;
    }

    :global(.color-box) {
        position: relative;
        z-index: 5;
        mix-blend-mode: normal;
    }
</style>