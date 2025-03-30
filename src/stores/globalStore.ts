import { writable } from 'svelte/store';

export const audioEnabled = writable(false);
export const terminalColor = writable('#00ff00');
export const isLoggedIn = writable(false);
export const userID = writable('');