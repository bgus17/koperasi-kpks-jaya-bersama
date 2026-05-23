import { APP_CONFIG } from './config.js';

export function getSession() {
    const raw = window.sessionStorage.getItem(APP_CONFIG.tokenStorageKey);

    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch {
        clearSession();
        return null;
    }
}

export function setSession(session) {
    window.sessionStorage.setItem(APP_CONFIG.tokenStorageKey, JSON.stringify({
        token: session.token,
        actor: session.actor,
        menus: session.menus ?? [],
        saved_at: new Date().toISOString(),
    }));
}

export function updateSession(partial) {
    const current = getSession() ?? {};
    setSession({ ...current, ...partial });
}

export function clearSession() {
    window.sessionStorage.removeItem(APP_CONFIG.tokenStorageKey);
}

export function getToken() {
    return getSession()?.token ?? null;
}

export function isAuthenticated() {
    return Boolean(getToken());
}
