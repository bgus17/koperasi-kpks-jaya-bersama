import { escapeHtml, setDocumentTitle } from '../core/dom.js';
import { getSession } from '../core/session.js';
import { renderShell } from './shell.js';

export function renderDashboard(root) {
    setDocumentTitle('Dashboard');
    const session = getSession();
    const menus = session?.menus ?? [];

    renderShell(root, `
        <div class="panel">
            <p class="eyebrow">Dashboard</p>
            <h1 class="title">Form sesuai akses aktor</h1>
            <p class="subtitle">Pilih menu untuk mengambil schema form dari API. Setiap submit tetap divalidasi ulang oleh server berdasarkan role user.</p>
        </div>
        <div class="menu-grid">
            ${menus.map((menu) => `
                <a class="menu-card" href="${menu.type === 'pengeluaran' || menu.type === 'rekap' ? `#/form/${menu.slug}` : '#/dashboard'}">
                    <strong>${escapeHtml(menu.label)}</strong>
                    <span>${menu.capabilities?.map(escapeHtml).join(' / ') || escapeHtml(menu.type)}</span>
                </a>
            `).join('')}
        </div>
    `);
}
