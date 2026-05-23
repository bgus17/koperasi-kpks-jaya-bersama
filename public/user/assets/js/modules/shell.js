import { apiRequest } from '../core/http.js';
import { escapeHtml } from '../core/dom.js';
import { clearSession, getSession, updateSession } from '../core/session.js';
import { navigate } from '../core/router.js';
import { securityContext } from '../core/security.js';

export async function ensureFreshSession() {
    const current = getSession();

    if (!current?.token) {
        return null;
    }

    const response = await apiRequest('/auth/me');
    updateSession({
        token: current.token,
        actor: response.actor,
        menus: response.menus,
    });

    return response;
}

export function renderShell(root, contentHtml, activeSlug = '') {
    const session = getSession();
    const actor = session?.actor ?? {};
    const menus = session?.menus ?? [];
    const security = securityContext();

    root.innerHTML = `
        <div class="app-shell">
            <aside class="sidebar">
                <div>
                    <div class="brand-mark">KC</div>
                    <h1>Koperasi<br>Cahaya Mulya</h1>
                    <small>Portal User API</small>
                </div>
                <nav>
                    ${menus.map((menu) => menuLink(menu, activeSlug)).join('')}
                </nav>
                <div class="actor-card">
                    <strong>${escapeHtml(actor.name)}</strong>
                    <span>${escapeHtml(actor.role_label ?? actor.role)}</span>
                    <button class="btn btn-outline" type="button" data-logout style="width:100%;margin-top:14px;">Logout</button>
                </div>
            </aside>
            <main class="main">
                <header class="topbar">
                    <div>
                        <p class="eyebrow">Portal User</p>
                        <strong>${escapeHtml(actor.role_label ?? 'User')}</strong>
                    </div>
                    <button class="btn btn-ghost" type="button" data-refresh>Refresh Session</button>
                </header>
                <section class="content">
                    ${security.warning ? `<div class="notice error" style="margin-bottom:16px;">${escapeHtml(security.warning)}</div>` : ''}
                    ${contentHtml}
                </section>
            </main>
        </div>
    `;

    root.querySelector('[data-logout]')?.addEventListener('click', async () => {
        try {
            await apiRequest('/auth/logout', { method: 'POST' });
        } finally {
            clearSession();
            navigate('login');
        }
    });

    root.querySelector('[data-refresh]')?.addEventListener('click', async () => {
        await ensureFreshSession();
        navigate('dashboard');
    });
}

function menuLink(menu, activeSlug) {
    const href = menu.type === 'pengeluaran' || menu.type === 'rekap'
        ? `#/form/${menu.slug}`
        : '#/dashboard';
    const active = activeSlug === menu.slug ? ' is-active' : '';

    return `
        <a class="menu-card${active}" href="${href}" style="min-height:auto;margin-bottom:10px;">
            <strong>${escapeHtml(menu.label)}</strong>
            <span>${escapeHtml(menu.type)}</span>
        </a>
    `;
}
