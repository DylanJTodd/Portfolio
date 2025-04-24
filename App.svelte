<script lang="ts">
  import { currentRoute, breadcrumbs, navigateTo } from './src/stores/routeStore';
  import { lowGraphics, fontSize, terminalColor } from './src/stores/globalStore';

  import MainConfig from './src/pages/MainConfig.svelte';
  import Navigation from './src/pages/Navigate.svelte';
  import About from './src/pages/About.svelte';
  import Projects from './src/pages/Projects.svelte';
  import SQLSquidGames from './src/pages/SQLProject.svelte';
  import Login from './src/pages/Login.svelte';
  import Notes from './src/pages/Notes.svelte';
  import Settings from './src/pages/Settings.svelte';
  import Experience from './src/pages/Experience.svelte';
  import Contact from './src/pages/ContactMe.svelte';
  import ThumbGenie from './src/pages/ThumbGenie.svelte';
  import Kaggle from './src/pages/Kaggle.svelte';
  import FBRedesign from './src/pages/FacebookRedesign.svelte';
  import MessageManager from './src/pages/ManageMessages.svelte';

  import TerminalEffect from './src/components/terminaleffect.svelte';
  import CursorSVG from './src/components/cursorsvg.svelte';
  import ColorFilter from './src/components/colorfilter.svelte';

  $: route = $currentRoute;
  $: isLowGraphics = $lowGraphics;
  $: currentFontSize = $fontSize;
  $: baseColor = $terminalColor; // Use the imported store value

  $: if (typeof document !== 'undefined') {
    document.documentElement.style.setProperty('--font-size-multiplier', currentFontSize.toString());
  }

  navigateTo('mainConfig');

  const components = {
    mainConfig: MainConfig,
    navigation: Navigation,
    about: About,
    projects: Projects,
    sql_squid_games: SQLSquidGames,
    login: Login,
    notes: Notes,
    experience: Experience,
    thumbgenie: ThumbGenie,
    settings: Settings,
    contact: Contact,
    kaggle: Kaggle,
    fbredesign: FBRedesign,
    messagemanager: MessageManager,
  };

  $: CurrentPageComponent = components[route] || Navigation;

</script>

<CursorSVG />

{#if isLowGraphics}
  <ColorFilter>
      {#if route !== 'mainConfig' && route !== 'login'}
        <nav class="breadcrumb" style="--breadcrumb-base-color: {baseColor};">
          {#each $breadcrumbs as crumb, index}
            <button
              type="button"
              class="breadcrumb-item"
              on:click={() => navigateTo(crumb)}
              on:keydown={(e) => { if (e.key === 'Enter' || e.key === ' ') navigateTo(crumb); }}
            >
              {crumb.includes('/') ? crumb.split('/').pop() : crumb}
            </button>
            {#if index < $breadcrumbs.length - 1}
              <span class="breadcrumb-separator">></span>
            {/if}
          {/each}
        </nav>
      {/if}
      <main class="mainstuff">
         <svelte:component this={CurrentPageComponent} />
      </main>
  </ColorFilter>
{:else}
  <TerminalEffect>
    <ColorFilter>
        {#if route !== 'mainConfig' && route !== 'login'}
          <nav class="breadcrumb" style="--breadcrumb-base-color: {baseColor};">
            {#each $breadcrumbs as crumb, index}
              <button
                type="button"
                class="breadcrumb-item"
                on:click={() => navigateTo(crumb)}
                on:keydown={(e) => { if (e.key === 'Enter' || e.key === ' ') navigateTo(crumb); }}
              >
                 {crumb.includes('/') ? crumb.split('/').pop() : crumb}
              </button>
              {#if index < $breadcrumbs.length - 1}
                <span class="breadcrumb-separator">></span>
              {/if}
            {/each}
          </nav>
        {/if}
        <main class="mainstuff">
           <svelte:component this={CurrentPageComponent} />
        </main>
    </ColorFilter>
  </TerminalEffect>
{/if}

<style>
  :global(html) {
    font-size: calc((1rem + 1vw) * var(--font-size-multiplier, 1));
  }
</style>