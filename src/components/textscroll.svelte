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
    let isTyping = false;
    let skipAnimation = false;
    let componentElement: HTMLDivElement;
    let lastSkipTime = 0;
    const skipCooldown = 1000;
    let animationStartTime = 0;
    const minAnimationTimeBeforeSkip = 1000;
    
    $: showCaret = $activeInstance === instanceId && !hideCaretManually;
    
    function selectAudioFile(speed: number): [string, number] {
        if (speed <= 25) {
            const files = [
                ['/res/typing/fast1', 5.9],
                ['/res/typing/fast2', 5]
            ];
            return files[Math.floor(Math.random() * files.length)] as [string, number];
        } else {
            const files = [
                ['/res/typing/normal1', 9.05],
                ['/res/typing/normal2', 10.9]
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
    
    function handleKeydown(event: KeyboardEvent) {
        if (event.code === 'Space' && isTyping && $activeInstance === instanceId) {
            event.preventDefault();
            event.stopPropagation();
            
            const currentTime = Date.now();
            const timeSinceStart = currentTime - animationStartTime;
            
            if (timeSinceStart >= minAnimationTimeBeforeSkip && 
                currentTime - lastSkipTime >= skipCooldown) {
                
                skipAnimation = true;
                isTyping = false;
                lastSkipTime = currentTime;
                displayedText = text;
                
                if (audioPlay && typingAudio) {
                    typingAudio.pause();
                    typingAudio.currentTime = 0;
                }
            }
        }
    }
    
    onMount(() => {
        window.addEventListener('keydown', handleKeydown);
        queue.push(animate);
        processQueue();
        return () => {
            window.removeEventListener('keydown', handleKeydown);
        };
    });
    
    async function animate() {
        if (isTyping) return;
        hasStarted = true;
        isAnyScrolling.set(true);
        activeInstance.set(instanceId);
        isTyping = true;
        animationStartTime = Date.now();
    
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
        isTyping = false;
    
        const terminal = document.querySelector('.terminal-opening');
        if (terminal) {
            terminal.scrollTo({ top: terminal.scrollHeight, behavior: 'smooth' });
        }
    
        dispatch('animationComplete');
    }
    
    async function typeText() {
        const terminal = document.querySelector('.terminal-opening');
        if (!terminal || !componentElement) return; // Ensure elements exist
    
        const screenHeight = terminal.clientHeight;
    
        for (let i = 0; i <= text.length; i++) {
            if (skipAnimation) {
                displayedText = text;
                skipAnimation = false;
                break;
            }
    
            displayedText = text.slice(0, i);
    
            // Auto-scrolling logic
            try {
                const contentBottom = componentElement.offsetTop + componentElement.offsetHeight;
                const scrollThreshold = terminal.scrollTop + screenHeight * 0.75;
    
                if (contentBottom > scrollThreshold) {
                    const scrollAmount = contentBottom - scrollThreshold;
                    terminal.scrollBy({ top: scrollAmount, behavior: 'smooth' });
                }
            } catch (error) {
                console.error("Error during auto-scrolling:", error);
            }
    
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
    
<div class="terminal-text" bind:this={componentElement}>
    <p class="text-content">
        {displayedText}{#if showCaret}<span class="caret" class:visible={isCaretVisible}></span>{/if}
    </p>
</div>