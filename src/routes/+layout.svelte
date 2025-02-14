<script lang="ts">
	import '/src/app.css';
	let { children } = $props();
	import TerminalEffect from '$lib/components/terminaleffect.svelte';
	import CursorSVG from '$lib/components/cursorsvg.svelte'; // adjust the import path
</script>

<CursorSVG></CursorSVG>
<main>
	<TerminalEffect>
		{@render children()}
	</TerminalEffect>
</main>
