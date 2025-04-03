import { writable } from 'svelte/store';

export const currentRoute = writable('navigation');
export const breadcrumbs = writable<string[]>(['navigation']);

export function navigateTo(route: string) {
    currentRoute.set(route);

    breadcrumbs.update((crumbs) => {
        if (route === 'navigation') {
            return ['navigation'];
        }

        const routeIndex = crumbs.indexOf(route);
        if (routeIndex !== -1) {
            return crumbs.slice(0, routeIndex + 1);
        }

        return [...crumbs, route];
    });
}