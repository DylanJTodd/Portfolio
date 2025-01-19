<video 
  bind:this={video} 
  width="100%" 
>
    <source src="/bootvideo.mp4" type="video/mp4">
</video>

<script>
    import { onMount } from 'svelte';
    let video;

    onMount(() => 
    {
        video.oncanplaythrough = () => 
        {
            video.play();
        };
    });
    
</script>

<style>
    video {
      position: absolute;
      top: 0;
      left: 0;
      object-fit: cover;
      width: 100%;
    }
  
    html, body {
      margin: 0;
      padding: 0;
      overflow: hidden;
      width: 100%;
      height: 100%;
    }
  </style>