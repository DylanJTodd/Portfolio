<script lang="ts">
    import Scanlines from '../components/scanlines.svelte';
    import TextFlicker from '../components/textflicker.svelte';
    import ColorFilter from '../components/colorfilter.svelte';
    import TextGlow from '../components/textglow.svelte';

    // Properties for ColorFilter
    export let opacity: number = 0.4;
    
    // Properties for Scanlines
    export let scanWidth: string = '2px';
    export let scanMovingLineWidth: string = '50vh';
    export let scanCRT: boolean = true;
    export let scanFPS: number = 60;
    export let scanColor: string = 'rgba(0, 0, 0, 0.2)';
    export let scanMovingColor: string = 'rgba(255, 255, 255, 0.01)';
    export let scanZIndex: number = 2;
    export let scanMovingLine: boolean = true;
  
    // Properties for TextFlicker
    export let flickerColor: string = 'color-mix(in srgb, currentColor 20%, transparent)';
    export let baseOffset: number = 2.5;
  
    // Properties for TextGlow
    export let glowColor: string = '#ffffff';
    export let glowSpread: string = '8px';
    export let glowOpacity: number = 0.1;
  </script>
  
  <Scanlines 
    {scanWidth}
    {scanMovingLineWidth}
    {scanCRT}
    {scanFPS}
    {scanColor}
    {scanMovingColor}
    {scanZIndex}
    {scanMovingLine}
  >
    <TextFlicker 
      {flickerColor}
      {baseOffset}
    >
      <ColorFilter {opacity}>
        <TextGlow 
          {glowColor}
          {glowSpread}
          {glowOpacity}
        >
          <slot></slot>
        </TextGlow>
      </ColorFilter>
    </TextFlicker>
  </Scanlines>  