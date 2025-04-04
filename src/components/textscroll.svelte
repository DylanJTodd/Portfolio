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
    import { onDestroy, onMount, createEventDispatcher, tick } from 'svelte';
    import { audioLevel } from '../stores/globalStore';

    const dispatch = createEventDispatcher();

    export let text: string = "";
    export let typingSpeed: number = 50;
    export let startDelay: number = 0;
    export let hideCaretManually: boolean = false;
    export let audioPlay: boolean = true;

    let displayedText: string = "";
    let isCaretVisible: boolean = true;
    let typingAudio: HTMLAudioElement | null = null;
    let audioLength: number = 0;
    let hasStarted = false;
    let instanceId = crypto.randomUUID();
    let isTyping = false;
    let skipAnimation = false;
    let componentElement: HTMLDivElement;
    let lastSkipTime = 0;
    const skipCooldown = 750;
    let animationStartTime = 0;
    const minAnimationTimeBeforeSkip = 750;
    let unsubscribeAudioLevel: (() => void) | null = null;

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

    function setupAudio() {
         if (typeof window !== 'undefined' && audioPlay && !typingAudio) {
            const [audioFile, duration] = selectAudioFile(typingSpeed);
            if (typeof Audio !== 'undefined') {
                try {
                    typingAudio = new Audio(audioFile + '.mp3');
                    audioLength = duration;
                    typingAudio.loop = true;
                    unsubscribeAudioLevel = audioLevel.subscribe(level => {
                        if (typingAudio) {
                            typingAudio.volume = level / 100;
                        }
                    });
                } catch (e) {
                     console.error("Failed to create Audio object:", e);
                     typingAudio = null;
                }
            }
        }
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
                displayedText = text;
                lastSkipTime = currentTime;

                if (audioPlay && typingAudio) {
                    typingAudio.pause();
                    typingAudio.currentTime = 0;
                }
            }
        }
    }

    let keydownHandlerRef = handleKeydown;
    let blinkInterval: ReturnType<typeof setInterval>;

    onMount(() => {
        setupAudio();
        window.addEventListener('keydown', keydownHandlerRef);
        queue.push(animate);
        processQueue();

        blinkInterval = setInterval(() => {
            isCaretVisible = !isCaretVisible;
        }, 175);

        return () => {
            window.removeEventListener('keydown', keydownHandlerRef);
            clearInterval(blinkInterval);

            if (typingAudio) {
                typingAudio.pause();
                typingAudio.src = '';
                typingAudio = null;
            }
            if (unsubscribeAudioLevel) {
                 unsubscribeAudioLevel();
            }
            if ($activeInstance === instanceId) {
                activeInstance.set(null);
            }
            const index = queue.indexOf(animate);
            if (index > -1) {
                queue.splice(index, 1);
            }
        };
    });

    async function animate() {
        if (isTyping) return;
        hasStarted = true;
        isAnyScrolling.set(true);
        activeInstance.set(instanceId);
        isTyping = true;
        skipAnimation = false;
        animationStartTime = Date.now();

        if (startDelay > 0) {
            await new Promise(resolve => setTimeout(resolve, startDelay));
        }

        if (audioPlay && typingAudio && text && !skipAnimation) {
             try {
                const totalAnimationTime = (text.length * typingSpeed) / 1000;
                const startTime = audioLength > 0 ? audioLength - totalAnimationTime : 0;
                typingAudio.currentTime = Math.max(0, startTime);
                typingAudio.volume = $audioLevel / 100;
                await typingAudio.play();
             } catch (error) {
                console.warn("Audio play failed:", error);
             }
        }

        await typeText();

        if (audioPlay && typingAudio) {
             if (typingAudio.duration > 0 && !typingAudio.paused) {
                 const fadePoints = 5;
                 const fadeTime = 200;
                 let fadeIntervalId: ReturnType<typeof setInterval> | null = null;
                 const clearFadeInterval = () => {
                     if (fadeIntervalId !== null) {
                         clearInterval(fadeIntervalId);
                         fadeIntervalId = null;
                     }
                 };
                 clearFadeInterval();

                 fadeIntervalId = setInterval(() => {
                    if (!typingAudio) {
                        clearFadeInterval();
                        return;
                    }
                    if (typingAudio.volume > 0.1) {
                        typingAudio.volume = Math.max(0, typingAudio.volume - (1.0 / fadePoints));
                    } else {
                        clearFadeInterval();
                        typingAudio.pause();
                        typingAudio.volume = $audioLevel / 100;
                        typingAudio.currentTime = 0;
                    }
                 }, fadeTime / fadePoints);
            } else {
                typingAudio.pause();
                typingAudio.currentTime = 0;
                typingAudio.volume = $audioLevel / 100;
            }
        }


        isAnyScrolling.set(false);
        isTyping = false;

        dispatch('animationComplete');
        processQueue();
    }

    async function typeText() {
        const terminal = componentElement?.closest('.terminal-opening, .mainstuff');
        if (!terminal || !componentElement) { isTyping = false; return; }
        const screenHeight = terminal.clientHeight;

        for (let i = 0; i <= text.length; i++) {
            if (skipAnimation) {
                displayedText = text;
                break;
            }

            displayedText = text.slice(0, i);
            await tick();

            try {
                if (componentElement) {
                    const contentBottom = componentElement.offsetTop + componentElement.offsetHeight;
                    const scrollThreshold = terminal.scrollTop + screenHeight * 0.75;
                    if (contentBottom > scrollThreshold) {
                        const scrollAmount = contentBottom - scrollThreshold;
                        terminal.scrollBy({ top: scrollAmount });
                    }
                }
            } catch (error) { console.error("Error during auto-scrolling:", error); }

            await new Promise(resolve => setTimeout(resolve, typingSpeed));
        }
        skipAnimation = false;
        isTyping = false;
    }
</script>

<!-- HTML: Restored Original Caret Span -->
<div class="terminal-text" bind:this={componentElement}>
    <p class="text-content">
        {displayedText}{#if showCaret}<span class="caret" class:visible={isCaretVisible}></span>{/if}
    </p>
</div>

<!-- CSS: Restored Original Caret Styles -->
<style>
    .caret {
        display: inline-block;
        width: 10px;
        height: 1em;
        background-color: #d1d1d1;
        opacity: 0; /* Start hidden */
        position: relative;
        vertical-align: text-bottom;
        margin-left: 1px;
        margin-bottom: 3px;
    }
    /* Class toggled by JS controls visibility */
    .caret.visible {
        opacity: 1;
    }

    /* Optional text-content styles */
    /* .text-content {
         white-space: pre-wrap;
         word-break: break-word;
    } */
</style>