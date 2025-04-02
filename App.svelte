<script>
  import { currentRoute, navigateTo } from './src/stores/routeStore';
  import { lowGraphics, fontSize } from './src/stores/globalStore';

  import MainConfig from './src/pages/MainConfig.svelte';
  import Navigation from './src/pages/Navigate.svelte';
  import About from './src/pages/About.svelte';
  import Projects from './src/pages/Projects.svelte';
  import SQLSquidGames from './src/pages/SQLProject.svelte';
  import Login from './src/pages/Login.svelte';
  import Notes from './src/pages/Notes.svelte';
  import Settings from './src/pages/Settings.svelte';
  import Contact from './src/pages/ContactMe.svelte';

  import TerminalEffect from './src/components/terminaleffect.svelte';
  import CursorSVG from './src/components/cursorsvg.svelte';
  import ColorFilter from './src/components/colorfilter.svelte';

  let route;
  $: currentRoute.subscribe(value => route = value);

  let isLowGraphics;
  $: lowGraphics.subscribe(value => isLowGraphics = value);

  let currentFontSize;
  $: fontSize.subscribe(value => {
    currentFontSize = value;
    document.documentElement.style.setProperty('--font-size-multiplier', value);
  });

  navigateTo('mainConfig');
</script>

<CursorSVG />
{#if !isLowGraphics}
  <TerminalEffect>
    <ColorFilter>
      <main class="mainstuff">
        {#if route === 'mainConfig'}
          <MainConfig />
        {:else if route === 'navigation'}
          <Navigation />
        {:else if route === 'about'}
          <About />
        {:else if route === 'projects'}
          <Projects />
        {:else if route === 'projects/sql_squid_games'}
          <SQLSquidGames />
        {:else if route === 'login'}
          <Login />
        {:else if route === 'notes'}
          <Notes />
        {:else if route === 'settings'}
          <Settings />
        {:else if route === 'contact'}
          <Contact />
        {/if}
      </main>
    </ColorFilter>
  </TerminalEffect>
{:else}
  <ColorFilter>
    <main class="mainstuff">
      {#if route === 'mainConfig'}
        <MainConfig />
      {:else if route === 'navigation'}
        <Navigation />
      {:else if route === 'about'}
        <About />
      {:else if route === 'projects'}
        <Projects />
      {:else if route === 'projects/sql_squid_games'}
        <SQLSquidGames />
      {:else if route === 'login'}
        <Login />
      {:else if route === 'notes'}
        <Notes />
      {:else if route === 'settings'}
        <Settings />
      {:else if route === 'contact'}
        <Contact />
      {/if}
    </main>
  </ColorFilter>
{/if}

<style>
  :global(html) {
    font-size: calc((1rem + 1vw) * var(--font-size-multiplier, 1));
  }
</style>