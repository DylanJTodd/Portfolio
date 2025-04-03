<script lang="ts" context="module">
    // --- Module script remains the same ---
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
    // --- Instance script keeps scrolling fixes, restores original caret logic ---
    import { onDestroy, onMount, createEventDispatcher, tick } from 'svelte';
    import { audioLevel } from '../stores/globalStore';

    const dispatch = createEventDispatcher();

    export let text: string = "";
    export let typingSpeed: number = 50;
    export let startDelay: number = 0;
    export let hideCaretManually: boolean = false;
    export let audioPlay: boolean = true;

    let displayedText: string = "";
    // *** Restored: Variable for caret visibility ***
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

    // *** Restored: Computed variable for showing caret ***
    $: showCaret = $activeInstance === instanceId && !hideCaretManually;

    // selectAudioFile remains the same
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

    // setupAudio remains the same
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

     // handleKeydown: Restore original logic exactly
     function handleKeydown(event: KeyboardEvent) {
        if (event.code === 'Space' && isTyping && $activeInstance === instanceId) {
            event.preventDefault();
            event.stopPropagation();

            const currentTime = Date.now();
            const timeSinceStart = currentTime - animationStartTime;

            if (timeSinceStart >= minAnimationTimeBeforeSkip &&
                currentTime - lastSkipTime >= skipCooldown) {

                skipAnimation = true;
                // *** Original also set these here ***
                isTyping = false;
                displayedText = text;
                // *** --- ***
                lastSkipTime = currentTime;

                if (audioPlay && typingAudio) {
                    typingAudio.pause();
                    typingAudio.currentTime = 0;
                }
            }
        }
    }

    let keydownHandlerRef = handleKeydown;
    // *** Restored: Variable for blink interval ID ***
    let blinkInterval: ReturnType<typeof setInterval>;

    onMount(() => {
        setupAudio();
        // *** Restored: Original keydown listener setup ***
        window.addEventListener('keydown', keydownHandlerRef);
        queue.push(animate);
        processQueue();

        // *** Restored: Original blink interval setup ***
        blinkInterval = setInterval(() => {
            isCaretVisible = !isCaretVisible;
        }, 175); // Original interval time

        return () => { // Combined cleanup
             // *** Restored: Original keydown listener removal ***
            window.removeEventListener('keydown', keydownHandlerRef);
             // *** Restored: Original blink interval clearing ***
            clearInterval(blinkInterval);

            // Other cleanup remains
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

    // animate: Keeps the *removal* of the final scroll at the end
    // Also keeps the improved audio stop logic (no fade unless desired)
    async function animate() {
        if (isTyping) return; // Check added previously
        hasStarted = true;
        isAnyScrolling.set(true);
        activeInstance.set(instanceId);
        isTyping = true; // Set state here
        skipAnimation = false; // Reset skip flag
        animationStartTime = Date.now();

        if (startDelay > 0) {
            await new Promise(resolve => setTimeout(resolve, startDelay));
        }

        // Audio play logic (original restored, but check typingAudio exists)
        if (audioPlay && typingAudio && text && !skipAnimation) {
             try {
                const totalAnimationTime = (text.length * typingSpeed) / 1000;
                // Ensure audioLength is valid before using it
                const startTime = audioLength > 0 ? audioLength - totalAnimationTime : 0;
                typingAudio.currentTime = Math.max(0, startTime);
                // Apply current volume before playing
                typingAudio.volume = $audioLevel / 100;
                await typingAudio.play();
             } catch (error) {
                console.warn("Audio play failed:", error);
             }
        }

        await typeText(); // This will now set isTyping=false if skip happens

        // Audio stop logic: Restore original fade-out
        if (audioPlay && typingAudio) {
            // Check if fade logic should only run if audio was actually played
             if (typingAudio.duration > 0 && !typingAudio.paused) {
                 const fadePoints = 5;
                 const fadeTime = 200;
                 // Use a flag to prevent multiple intervals if called rapidly
                 let fadeIntervalId: ReturnType<typeof setInterval> | null = null;
                 const clearFadeInterval = () => {
                     if (fadeIntervalId !== null) {
                         clearInterval(fadeIntervalId);
                         fadeIntervalId = null;
                     }
                 };
                 // Clear any previous interval just in case
                 clearFadeInterval();

                 fadeIntervalId = setInterval(() => {
                    // Check if audio still exists
                    if (!typingAudio) {
                        clearFadeInterval();
                        return;
                    }
                    if (typingAudio.volume > 0.1) {
                        // Ensure volume doesn't go negative
                        typingAudio.volume = Math.max(0, typingAudio.volume - (1.0 / fadePoints));
                    } else {
                        clearFadeInterval();
                        typingAudio.pause();
                        typingAudio.volume = $audioLevel / 100; // Reset to global level
                        typingAudio.currentTime = 0;
                    }
                 }, fadeTime / fadePoints);
            } else {
                // If audio wasn't playing, just ensure it's paused/reset
                typingAudio.pause();
                typingAudio.currentTime = 0;
                typingAudio.volume = $audioLevel / 100;
            }
        }


        // Reset state (original placement) - isTyping might already be false if skipped
        isAnyScrolling.set(false);
        isTyping = false; // Ensure it's false here


        // *** FINAL SCROLL LOGIC REMAINS REMOVED ***
        // const terminal = document.querySelector('.terminal-opening');
        // if (terminal) {
        //     terminal.scrollTo({ top: terminal.scrollHeight, behavior: 'smooth' });
        // }

        dispatch('animationComplete');
        processQueue(); // Keep this
    }

    // typeText: Keeps the *removal* of 'behavior: smooth' from auto-scroll
    async function typeText() {
        const terminal = componentElement?.closest('.terminal-opening, .mainstuff');
        if (!terminal || !componentElement) { isTyping = false; return; } // Keep checks
        const screenHeight = terminal.clientHeight;

        for (let i = 0; i <= text.length; i++) {
            // Skip logic: Original placement of setting displayedText and breaking
            if (skipAnimation) {
                displayedText = text; // Set final text on skip
                // isTyping = false; // Already set in handleKeydown now
                // skipAnimation = false; // Reset after loop is better
                break;
            }

            displayedText = text.slice(0, i);
            await tick(); // Keep tick before scroll check

            try { // Auto-scroll logic
                if (componentElement) {
                    const contentBottom = componentElement.offsetTop + componentElement.offsetHeight;
                    const scrollThreshold = terminal.scrollTop + screenHeight * 0.75;
                    if (contentBottom > scrollThreshold) {
                        const scrollAmount = contentBottom - scrollThreshold;
                        // *** KEEP behavior: 'smooth' REMOVED ***
                        terminal.scrollBy({ top: scrollAmount });
                    }
                }
            } catch (error) { console.error("Error during auto-scrolling:", error); }

            await new Promise(resolve => setTimeout(resolve, typingSpeed));
        }
        // Reset skip flag *after* loop/break
        skipAnimation = false;
        // isTyping might have been set false by skip, ensure it here if loop completed normally
        isTyping = false;
    }

    // *** Restored: Original onDestroy logic ***
    // Note: Combined cleanup is now in onMount return function,
    // so this separate onDestroy is not strictly needed unless adding new logic.
    // The onMount return function is the preferred Svelte 3+ way.
    // onDestroy(() => {
    //     if (audioPlay && typingAudio) {
    //         typingAudio.pause();
    //         typingAudio.currentTime = 0;
    //     }
    //     if ($activeInstance === instanceId) {
    //         activeInstance.set(null);
    //     }
    //     const index = queue.indexOf(animate);
    //     if (index > -1) {
    //         queue.splice(index, 1);
    //     }
    // });

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