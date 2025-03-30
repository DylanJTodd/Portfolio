<script lang="ts">
    import TextScroll from '../components/textscroll.svelte';
    import ChoiceSelector from '../components/choiceselector.svelte';
    import { audioEnabled, userID } from '../stores/globalStore';
    import { navigateTo } from '../stores/routeStore';
    import { onMount } from 'svelte';

    let notes: { id: number; created_at: string; content?: string }[] = []; // Add optional content field
    let currentStep = 1;
    let selectedNoteId: number | null = null;
    let selectedNoteIds: Set<number> = new Set();
    let noteContent = '';
    let typedCharacterCount = 0;
    let intervalId: any;
    let isTyping = false;

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
                noteContent = note.content; // Update noteContent for the textarea
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
        }
    }

    async function deleteSelectedNotes() {
        for (const id of selectedNoteIds) {
            await deleteNote(id);
        }
        selectedNoteIds.clear();
        await fetchNotes();
        currentStep = 1;
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

    function toggleNoteSelection(index: number) {
        const noteId = notes[index - 2].id; // Adjust index for "Cancel" and "Delete Selected Notes"
        if (selectedNoteIds.has(noteId)) {
            selectedNoteIds.delete(noteId);
        } else {
            selectedNoteIds.add(noteId);
        }
    }

    onMount(() => {
        if (!$userID || $userID === '') {
            console.error('You are not logged in');
        }
        fetchNotes();
        return () => stopAutoSave();
    });

    $: if (currentStep === 1) {
        fetchNotes(); // Re-fetch notes whenever we enter step 1
    }
</script>

{#if !$userID || $userID === ''}
    <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="You are not logged in" />
{:else if currentStep === 1}
    <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="What would you like to do?" />
    <ChoiceSelector
        choices={['Create a Note', 'Delete a Note', 'Back', ...notes.map(note => note.created_at)]}
        isActive={!isTyping}
        onSelect={async (index) => {
            if (index === 0) createNote();
            else if (index === 1) currentStep = 3;
            else if (index === 2) navigateTo('navigation');
            else {
                selectedNoteId = notes[index - 3].id;
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
        choices={['Create Note', 'Back']}
        isActive={!isTyping}
        onSelect={async (index) => {
            if (index === 0) {
                await saveNote();
                currentStep = 1;
            } else if (index === 1) {
                if (selectedNoteId !== null) {
                    await deleteNote(selectedNoteId);
                }
                currentStep = 1;
            }
        }}
    />
{:else if currentStep === 3}
    <TextScroll audioPlay={$audioEnabled} typingSpeed={50} text="Select notes to delete or cancel." />
    <ChoiceSelector
        choices={['Cancel', 'Delete Selected Notes', ...notes.map(note => note.created_at)]}
        isActive={!isTyping}
        onSelect={(index) => {
            if (index === 0) currentStep = 1;
            else if (index === 1) deleteSelectedNotes();
            else toggleNoteSelection(index);
        }}
    />
{/if}

<style>
    textarea {
        resize: none;
        border: 1px solid #555;
    }
</style>