<script lang="ts">
	import '/src/app.css';
	let { children } = $props();
	import TerminalEffect from '$lib/components/terminaleffect.svelte';
</script>
<main>
	<TerminalEffect>
		{@render children()}
	</TerminalEffect>
</main>
