<!-- CursorSVG.svelte -->
<script>
    import { terminalColor } from '../stores/globalStore';
    import { onMount } from 'svelte';
    
    $: darkerColor = $terminalColor.replace('#', '').match(/.{2}/g)
      .map(hex => Math.max(0, parseInt(hex, 16) - 130).toString(16).padStart(2, '0'))
      .join('');

    $: svgString = `<svg xmlns="http://www.w3.org/2000/svg" width="35" height="40" viewBox="0 0 65 65">
        <defs>
            <linearGradient id="cursorGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:${$terminalColor};stop-opacity:1" />
                <stop offset="100%" style="stop-color:#${darkerColor};stop-opacity:1" />
            </linearGradient>
            <filter id="dropshadow" x="-10%" y="-10%" width="150%" height="150%">
                <feOffset result="offOut" in="SourceAlpha" dx="6" dy="8" />
                <feGaussianBlur result="blurOut" in="offOut" stdDeviation="3" />
                <feColorMatrix result="matrixOut" in="blurOut" type="matrix" 
                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.7 0"/>
                <feMerge>
                    <feMergeNode/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
        </defs>
        <g transform="translate(0,65) scale(0.1,-0.1)">
            <path d="M40 337 c0 -141 2 -257 5 -257 3 0 37 34 76 75 71 74 71 74 175 77 104 3 104 3 -76 183 -180 180 -180 180 -180 -78z" 
                fill="url(#cursorGradient)" 
                filter="url(#dropshadow)"/>
        </g>
    </svg>`;

    $: base64Svg = btoa(svgString);
    $: cursorUrl = `url("data:image/svg+xml;base64,${base64Svg}") 0 0, auto`;

    let styleElement;

    $: if (styleElement) {
        styleElement.textContent = `* { cursor: ${cursorUrl} !important; }`;
    }

    onMount(() => {
        styleElement = document.createElement('style');
        styleElement.textContent = `* { cursor: ${cursorUrl} !important; }`;
        document.head.appendChild(styleElement);

        return () => {
            document.head.removeChild(styleElement);
        };
    });
</script>

<div class="cursor-area"></div>

<style>
    .cursor-area {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        pointer-events: none;
    }
</style>