<script lang="ts">
	import '../app.css';
	let { children } = $props();
	import TerminalEffect from '$lib/components/terminaleffect.svelte';
</script>
<TerminalEffect>
    <main>
		{@render children()}
    </main>
</TerminalEffect>
