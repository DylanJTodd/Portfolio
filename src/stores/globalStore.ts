import { writable } from 'svelte/store';

export const audioEnabled = writable(false);
export const terminalColor = writable('#00ff00');
export const fontSize = writable(1);
export const textSpeed = writable(1);
export const audioLevel = writable(100);
export const lowGraphics = writable(false);

export const isLoggedIn = writable(false);
export const userID = writable('');
export const lastLogin = writable('');