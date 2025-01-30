<script lang="ts">
	import '/src/app.css';
	let { children } = $props();
	import TerminalEffect from '$lib/components/terminaleffect.svelte';
</script>
<main style="margin-left: 10vw;">
	<TerminalEffect>
		{@render children()}
	</TerminalEffect>
</main>
