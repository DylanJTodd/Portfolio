<!-- src/lib/components/scanlines.svelte -->
<div class="scanlines">
    <slot></slot>
</div>

<style>
    .scanlines {
        --scan-width: 2px;
        --scan-moving-line-width: 60px;
        --scan-crt: true;
        --scan-fps: 60;
        --scan-color: rgba(0, 0, 0, 0.4);
        --scan-moving-color: rgba(255, 255, 255, 0.08);
        --scan-z-index: 2;
        --scan-moving-line: true;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        margin: 0;
        padding: 0;
    }

    .scanlines::before,
    .scanlines::after {
        display: block;
        pointer-events: none;
        content: '';
        position: absolute;
        margin: 0;
        padding: 0;
    }

    /* Traveling white line */
    .scanlines::before {
        width: 100%;
        height: var(--scan-moving-line-width);
        z-index: calc(var(--scan-z-index) + 1);
        background: var(--scan-moving-color);
        opacity: 1;
        animation: scanline 6s linear infinite;
        box-shadow: 0 0 10px 1px var(--scan-moving-color);
    }

    /* Static scanlines with wobble */
    .scanlines::after {
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: var(--scan-z-index);
        background: linear-gradient(
            to bottom,
            transparent 50%,
            var(--scan-color) 51%
        );
        background-size: 100% calc(var(--scan-width) * 2);
        animation: 
            scanlines 1s steps(var(--scan-fps)) infinite,
            wobble 3s ease-in-out infinite;
    }

    @keyframes scanline {
        0%, 25% {
            transform: translate3d(0, 100vh, 0);
        }
        100% {
            transform: translate3d(0, -100vh, 0);
        }
    }

    @keyframes scanlines {
        0% {
            background-position: 0 50%;
        }
    }

    @keyframes wobble {
        0% {
            transform: skewX(0deg) skewY(0deg);
        }
        25% {
            transform: skewX(0.25deg) skewY(0.1deg);
        }
        50% {
            transform: skewX(-0.25deg) skewY(-0.1deg);
        }
        75% {
            transform: skewX(0.25deg) skewY(0.1deg);
        }
        100% {
            transform: skewX(0deg) skewY(0deg);
        }
    }
</style>