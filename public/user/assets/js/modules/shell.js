import { apiRequest } from '../core/http.js';
import { escapeHtml } from '../core/dom.js';
import { menuHref } from '../core/navigation.js';
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
    const actorName = actor.name ?? 'User';
    const actorRole = actor.role_label ?? actor.role ?? 'User';
    const actorInitial = String(actorName).trim().charAt(0).toUpperCase() || 'U';
    const currentYear = new Date().getFullYear();

    root.innerHTML = `
        <div class="app-shell">
            <aside class="sidebar" id="user-sidebar">
                <div class="sidebar-brand">
                    <div class="brand-mark">KP</div>
                    <h1>KPKS<br>Jaya Bersama</h1>
                    <p>Portal User</p>
                </div>
                <nav class="sidebar-nav">
                    <div class="nav-label">Menu Akses</div>
                    ${menus.map((menu) => menuLink(menu, activeSlug)).join('')}
                </nav>
                <div class="sidebar-footer">
                    <div class="user-info">
                        <div class="user-avatar">${escapeHtml(actorInitial)}</div>
                        <div>
                            <div class="user-name">${escapeHtml(actorName)}</div>
                            <div class="user-role">${escapeHtml(actorRole)}</div>
                        </div>
                    </div>
                    <button class="btn-logout" type="button" data-logout>Logout</button>
                </div>
            </aside>
            <button class="mobile-sidebar-overlay" type="button" data-sidebar-close aria-label="Tutup menu"></button>
            <main class="main">
                <header class="topbar">
                    <div class="topbar-left">
                        <button class="mobile-menu-button" type="button" data-sidebar-toggle aria-label="Menu">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="topbar-title">
                            ${escapeHtml(actorRole)}
                        </div>
                    </div>
                    <div class="topbar-right">
                        <span class="badge-tahun">Tahun Buku ${currentYear}</span>
                        <button class="btn btn-outline btn-sm" type="button" data-refresh>Refresh</button>
                    </div>
                </header>
                <section class="content">
                    ${security.warning ? `<div class="notice error shell-warning">${escapeHtml(security.warning)}</div>` : ''}
                    ${contentHtml}
                </section>
            </main>
        </div>
    `;

    // Sidebar Toggling Logic for Mobile
    const toggleBtn = root.querySelector('[data-sidebar-toggle]');
    const overlay = root.querySelector('[data-sidebar-close]');
    
    const openSidebar = () => {
        document.body.classList.add('sidebar-open');
    };
    
    const closeSidebar = () => {
        document.body.classList.remove('sidebar-open');
    };
    
    toggleBtn?.addEventListener('click', () => {
        if (document.body.classList.contains('sidebar-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });
    
    overlay?.addEventListener('click', closeSidebar);
    
    // Close sidebar when clicking links inside sidebar on mobile
    root.querySelectorAll('.sidebar-menu-link').forEach((link) => {
        link.addEventListener('click', closeSidebar);
    });

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
    const active = activeSlug === menu.slug ? ' active' : '';

    return `
        <a class="nav-link sidebar-menu-link${active}" href="${escapeHtml(menuHref(menu))}">
            <span class="nav-dot"></span>
            <span>
                <strong>${escapeHtml(menu.label)}</strong>
                <span>${escapeHtml(menu.type)}</span>
            </span>
        </a>
    `;
}
