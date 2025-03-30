<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { audioEnabled, userID } from '../stores/globalStore';
    import { navigateTo } from '../stores/routeStore';
    import { onMount } from 'svelte';

    let notes: { note_id: number; id?: number; created_at: string; content?: string }[] = [];
    let indexToNoteId: { [key: number]: number } = {};
    let currentStep = 1;
    let selectedNoteId: number | null = null;
    let selectedNoteIds: Set<number> = new Set();
    let noteContent = '';
    let typedCharacterCount = 0;
    let intervalId: any;
    let isTyping = false;
    let isChoiceActive = true;
    let selectedIndexes = new Set<number>();

    async function fetchNotes() {
        if (!$userID || $userID === '') {
            console.error('User ID is not available');
            return;
        }
        try {
            const response = await fetch(`/api/notes?user_id=${$userID}`);
            if (response.ok) {
                const fetchedNotes = await response.json();
                notes = fetchedNotes.sort((a: { created_at: string }, b: { created_at: string }) =>
                    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
                );
                indexToNoteId = {};
                notes.forEach((note, i) => {
                    indexToNoteId[i + 2] = note.note_id;
                });
            } else {
                console.error('Failed to fetch notes:', await response.json());
            }
        } catch (error) {
            console.error('Error fetching notes:', error);
        }
    }

    async function fetchNoteContent(noteId: number) {
        try {
            const response = await fetch(`/api/notes/${noteId}`);
            if (response.ok) {
                const note = await response.json();
                noteContent = note.content;
            } else {
                console.error('Failed to fetch note content:', await response.json());
            }
        } catch (error) {
            console.error('Error fetching note content:', error);
        }
    }

    async function createNote() {
        if (!$userID || $userID === '') {
            console.error('User ID is not available');
            return;
        }
        const response = await fetch('/api/notes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content: '', user_id: $userID })
        });
        if (response.ok) {
            const newNote = await response.json();
            selectedNoteId = newNote.note_id;
            noteContent = '';
            currentStep = 2;
        } else {
            console.error('Failed to create note:', await response.json());
        }
    }

    async function deleteNote(noteId: number) {
        if (!$userID || $userID === '') {
            console.error('User ID is not available');
            return;
        }
        const response = await fetch(`/api/notes/${noteId}`, { method: 'DELETE' });
        if (!response.ok) {
            console.error('Failed to delete note:', await response.json());
        } else {
        }
    }

    async function saveNote() {
        if (selectedNoteId !== null) {
            await fetch(`/api/notes/${selectedNoteId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: noteContent })
            });
        }
    }

    function handleTyping(event: Event) {
        if (currentStep === 2) {
            typedCharacterCount++;
            if (typedCharacterCount >= 30) {
                saveNote();
                typedCharacterCount = 0;
            }
        }
    }

    function startAutoSave() {
        intervalId = setInterval(saveNote, 3000);
    }

    function stopAutoSave() {
        clearInterval(intervalId);
    }

    async function handleDeleteSelectedNotes() {
        
        const filteredIndexes = Array.from(selectedIndexes).filter(index => index >= 2);
        
        const notesToDelete = filteredIndexes
            .map(index => {
                return indexToNoteId[index];
            })
            .filter(id => id !== undefined);
            
        for (const id of notesToDelete) {
            await deleteNote(id);
        }

        selectedNoteIds.clear();
        selectedIndexes.clear();
        await fetchNotes();
        currentStep = 1;
        isChoiceActive = true;
    }

    function handleCancel() {
        currentStep = 1;
        selectedNoteIds.clear();
        selectedIndexes.clear();
        isChoiceActive = true;
    }

    onMount(() => {
        if (!$userID || $userID === '') {
            console.error('You are not logged in');
        }
        fetchNotes();
        return () => stopAutoSave();
    });

    $: if (currentStep === 1) {
        fetchNotes();
    }

    $: if (currentStep === 3) {
        isChoiceActive = true;
        fetchNotes();
    }
</script>

{#if !$userID || $userID === ''}
    <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="You are not logged in" />
    {navigateTo('navigate')}
{:else if currentStep === 1}
    <ChoiceSelector
        choices={['Create a Note', 'Delete a Note', 'Back', ...notes.map(note => note.created_at)]}
        isActive={!isTyping}
        onSelect={async (index) => {
            if (index === 0) {
                await createNote();
            } else if (index === 1) {
                await fetchNotes();
                currentStep = 3;
            } else if (index === 2) {
                navigateTo('navigation');
            } else {
                selectedNoteId = notes[index - 3].note_id;
                await fetchNoteContent(selectedNoteId);
                currentStep = 2;
            }
        }}
    />
{:else if currentStep === 2}
    <textarea
        bind:value={noteContent}
        on:input={handleTyping}
        on:focus={() => { isTyping = true; startAutoSave(); }}
        on:blur={() => { isTyping = false; stopAutoSave(); }}
        style="width: 100%; height: 300px; background-color: #333; color: white; font-size: 1rem; padding: 0.5rem;"
    ></textarea>
    <ChoiceSelector
        choices={['Save Note', 'Back']}
        isActive={!isTyping}
        onSelect={async (index) => {
            if (index === 0) {
                await saveNote();
                await fetchNotes();
                currentStep = 1;
            } else if (index === 1) {
                await saveNote();
                await fetchNotes();
                currentStep = 1;
            }
        }}
    />
{:else if currentStep === 3}
    <ChoiceSelector
        choices={['Cancel', 'Delete Selected Notes', ...notes.map(note => note.created_at)]}
        bind:isActive={isChoiceActive}
        multiple={true}
        bind:selectedIndexes={selectedIndexes}
        actionIndexes={[0, 1]}
        onSelect={async (index) => {
            if (index === 0) {
                handleCancel();
            } else if (index === 1) {
                await handleDeleteSelectedNotes();
            }
        }}
    />
{/if}

<style>
    textarea {
        resize: none;
        border: 1px solid #555;
    }
</style>