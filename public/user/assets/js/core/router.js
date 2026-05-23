const listeners = new Set();

export function getRoute() {
    const hash = window.location.hash.replace(/^#\/?/, '');

    return hash || 'dashboard';
}

export function navigate(route) {
    const normalized = route.replace(/^#\/?/, '');

    if (getRoute() === normalized) {
        emitRoute();
        return;
    }

    window.location.hash = `#/${normalized}`;
}

export function onRouteChange(callback) {
    listeners.add(callback);

    return () => listeners.delete(callback);
}

export function startRouter() {
    window.addEventListener('hashchange', emitRoute);
    emitRoute();
}

function emitRoute() {
    const route = getRoute();

    listeners.forEach((listener) => listener(route));
}
