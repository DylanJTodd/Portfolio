import { writable } from 'svelte/store';

//Settings
export const audioEnabled = writable(false);
export const terminalColor = writable('#00ff00');
export const fontSize = writable(1); // multiplier
export const textSpeed = writable(1); // multiplier
export const audioLevel = writable(100); // 0-100
export const lowGraphics = writable(false);

export const isLoggedIn = writable(false);
export const userID = writable('');
export const lastLogin = writable('');