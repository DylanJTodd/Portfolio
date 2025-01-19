<!-- src/lib/components/ColorFilter.svelte -->
<script lang="ts">
    export let color: string = '#00ff00';
    export let opacity: number = 0.4;
</script>

<div class="color-filter">
    <slot></slot>
    <div class="overlay-color" style="--filter-color: {color}; --filter-opacity: {opacity}"></div>
    <div class="overlay-desaturate"></div>
</div>

<style>
    .color-filter {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .overlay-color {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: var(--filter-color);
        opacity: var(--filter-opacity);
        z-index: 4;
        mix-blend-mode: color;
        pointer-events: none;
    }

    .overlay-desaturate {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: #808080;
        z-index: 3;
        mix-blend-mode: saturation;
        pointer-events: none;
    }
</style>