<script>
  import { currentRoute, navigateTo } from './src/stores/routeStore';

  import MainConfig from './src/pages/MainConfig.svelte';
  import Navigation from './src/pages/Navigate.svelte';
  import About from './src/pages/About.svelte';
  import Projects from './src/pages/Projects.svelte';
  import SQLSquidGames from './src/pages/SQLProject.svelte';
  import Login from './src/pages/Login.svelte';

  import TerminalEffect from './src/components/terminaleffect.svelte';
  import CursorSVG from './src/components/cursorsvg.svelte';
  import ColorFilter from './src/components/colorfilter.svelte';

  // Subscribe to the currentRoute store
  let route;
  $: currentRoute.subscribe(value => route = value);

  navigateTo('mainConfig');
</script>

<CursorSVG />
<TerminalEffect>
  <ColorFilter>
    <main>
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
      {/if}
    </main>
  </ColorFilter>
</TerminalEffect>