import { APP_CONFIG } from './config.js';
import { clearSession, getToken } from './session.js';
import { navigate } from './router.js';

export class ApiError extends Error {
    constructor(message, status, payload = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.payload = payload;
    }
}

export async function apiRequest(path, options = {}) {
    const url = resolveUrl(path);
    const headers = new Headers(options.headers ?? {});
    const token = getToken();

    headers.set('Accept', 'application/json');

    if (!(options.body instanceof FormData) && options.body !== undefined) {
        headers.set('Content-Type', 'application/json');
    }

    if (token) {
        headers.set('Authorization', `Bearer ${token}`);
    }

    const response = await fetch(url, {
        method: options.method ?? 'GET',
        headers,
        body: options.body instanceof FormData
            ? options.body
            : options.body !== undefined
                ? JSON.stringify(options.body)
                : undefined,
        credentials: 'same-origin',
    });

    const payload = await parseResponse(response);

    if (response.status === 401) {
        clearSession();
        navigate('login');
    }

    if (!response.ok) {
        throw new ApiError(payload?.message ?? 'Permintaan API gagal.', response.status, payload);
    }

    return payload;
}

function resolveUrl(path) {
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    if (path.startsWith('/api/')) {
        return `${window.location.origin}${path}`;
    }

    return `${APP_CONFIG.apiBaseUrl}/${path.replace(/^\/+/, '')}`;
}

async function parseResponse(response) {
    const contentType = response.headers.get('content-type') ?? '';

    if (!contentType.includes('application/json')) {
        return null;
    }

    return response.json();
}
