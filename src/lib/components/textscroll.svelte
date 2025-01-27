<!-- TextScroller.svelte -->
<script lang="ts" context="module">
    import { writable } from 'svelte/store';
    const isAnyScrolling = writable(false);
    const activeInstance = writable<string | null>(null);
    const queue: (() => Promise<void>)[] = [];
    let isProcessingQueue = false;
    
    async function processQueue() {
        if (isProcessingQueue || queue.length === 0) return;
        isProcessingQueue = true;
        while (queue.length > 0) {
            const nextAnimation = queue.shift();
            if (nextAnimation) {
                await nextAnimation();
            }
        }
        isProcessingQueue = false;
    }
</script>
    
<script lang="ts">
    import { onDestroy, onMount, createEventDispatcher } from 'svelte';
    
    const dispatch = createEventDispatcher();

    export let text: string = "";
    export let typingSpeed: number = 50;
    export let startDelay: number = 0;
    export let hideCaretManually: boolean = false;
    export let audioPlay: boolean = true;
    
    let displayedText: string = "";
    let isCaretVisible: boolean = true;
    let typingAudio: HTMLAudioElement;
    let audioLength: number;
    let hasStarted = false;
    let instanceId = crypto.randomUUID();
    
    $: showCaret = $activeInstance === instanceId && !hideCaretManually;
    
    function selectAudioFile(speed: number): [string, number] {
        if (speed <= 25) {
            const files = [
                ['/typing/fast1', 5.9],
                ['/typing/fast2', 5]
            ];
            return files[Math.floor(Math.random() * files.length)] as [string, number];
        } else {
            const files = [
                ['/typing/normal1', 9.05],
                ['/typing/normal2', 10.9]
            ];
            return files[Math.floor(Math.random() * files.length)] as [string, number];
        }
    }
    
    if (typeof window !== 'undefined' && audioPlay) {
        const [audioFile, duration] = selectAudioFile(typingSpeed);
        typingAudio = new Audio(audioFile + '.mp3');
        audioLength = duration;
        typingAudio.loop = true;
    }
    
    onMount(() => {
        queue.push(animate);
        processQueue();
    });
    
    async function animate() {
        hasStarted = true;
        isAnyScrolling.set(true);
        activeInstance.set(instanceId);
    
        if (startDelay > 0) {
            await new Promise(resolve => setTimeout(resolve, startDelay));
        }
    
        if (audioPlay && typingAudio && text) {
            const totalAnimationTime = (text.length * typingSpeed) / 1000;
            const startTime = audioLength - totalAnimationTime;
            typingAudio.currentTime = Math.max(0, startTime);
            typingAudio.play();
        }
    
        await typeText();
    
        if (audioPlay && typingAudio) {
            const fadePoints = 5;
            const fadeTime = 200;
            const fadeInterval = setInterval(() => {
                if (typingAudio.volume > 0.1) {
                    typingAudio.volume -= 1.0 / fadePoints;
                } else {
                    clearInterval(fadeInterval);
                    typingAudio.pause();
                    typingAudio.volume = 1.0;
                    typingAudio.currentTime = 0;
                }
            }, fadeTime / fadePoints);
        }
    
        isAnyScrolling.set(false);
        
        // Dispatch the animation complete event
        dispatch('animationComplete');
    }
    
    async function typeText() {
        for (let i = 0; i <= text.length; i++) {
            displayedText = text.slice(0, i);
            await new Promise(resolve => setTimeout(resolve, typingSpeed));
        }
    }
    
    onDestroy(() => {
        if (audioPlay && typingAudio) {
            typingAudio.pause();
            typingAudio.currentTime = 0;
        }
        if ($activeInstance === instanceId) {
            activeInstance.set(null);
        }
        const index = queue.indexOf(animate);
        if (index > -1) {
            queue.splice(index, 1);
        }
    });
    
    let blinkInterval: ReturnType<typeof setInterval>;
    
    onMount(() => {
        blinkInterval = setInterval(() => {
            isCaretVisible = !isCaretVisible;
        }, 175);
        return () => {
            clearInterval(blinkInterval);
        };
    });
</script>
    
<div class="terminal-text">
    <p class="text-content">
        {displayedText}{#if showCaret}<span class="caret" class:visible={isCaretVisible}></span>{/if}
    </p>
</div>
    
<style>
    .caret {
        display: inline-block;
        width: 10px;
        height: 1em;
        background-color: #d1d1d1;
        opacity: 0;
        position: relative;
        vertical-align: text-bottom;
        margin-left: 1px;
        margin-bottom: 3px;
    }
    
    .caret.visible {
        opacity: 1;
    }
</style>