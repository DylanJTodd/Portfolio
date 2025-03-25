import { writable } from 'svelte/store';

export const currentRoute = writable('navigation');

export function navigateTo(route: string) {
    currentRoute.set(route);
}