import { apiRequest } from '../core/http.js';
import { escapeHtml, qs, rupiah } from '../core/dom.js';

export function bindRekap(root) {
    const button = qs('[data-load-rekap]', root);

    if (!button) {
        return;
    }

    button.addEventListener('click', async () => {
        const year = qs('[data-rekap-year]', root).value || new Date().getFullYear();
        const month = qs('[data-rekap-month]', root)?.value || '';
        const target = qs('[data-rekap-result]', root);
        const params = new URLSearchParams({ tahun: year });

        if (month) {
            params.set('bulan', month);
        }

        target.innerHTML = '<div class="notice">Memuat rekap...</div>';

        try {
            const response = await apiRequest(`/rekap/laporan-lengkap?${params.toString()}`);
            const ledger = response.ledger_summary ?? {};
            const sections = response.laporan?.sections ?? [];

            target.innerHTML = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Keterangan</th>
                            <th>Debet</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${sections.map(sectionHtml).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>${escapeHtml(response.bulan ? `${response.bulan}-${response.tahun}` : response.tahun)}</td>
                            <td><strong>Grand Total</strong></td>
                            <td>${rupiah(ledger.total_debet)}</td>
                            <td>${rupiah(ledger.total_kredit)}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><strong>Saldo</strong></td>
                            <td></td>
                            <td></td>
                            <td>${rupiah(ledger.saldo_akhir)}</td>
                        </tr>
                    </tfoot>
                </table>
                <p class="muted">${escapeHtml(ledger.jumlah_record ?? 0)} transaksi aktual.</p>
            `;
        } catch (error) {
            target.innerHTML = `<div class="notice error">${escapeHtml(error.message)}</div>`;
        }
    });
}

function sectionHtml(section) {
    const rows = section.rows ?? [];

    return `
        <tr>
            <td><strong>${escapeHtml(section.nomor)}</strong></td>
            <td><strong>${escapeHtml(String(section.nama ?? '').toUpperCase())}</strong></td>
            <td>-</td>
            <td>-</td>
            <td></td>
        </tr>
        ${rows.map((row) => `
            <tr>
                <td>${escapeHtml(row.nomor)}</td>
                <td>${escapeHtml(row.keterangan)}</td>
                <td>${moneyOrDash(row.debet)}</td>
                <td>${moneyOrDash(row.kredit)}</td>
                <td>${row.has_data ? rupiah(row.saldo) : '-'}</td>
            </tr>
        `).join('')}
        ${rows.length === 0 ? `
            <tr>
                <td></td>
                <td>Belum ada data.</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
        ` : ''}
    `;
}

function moneyOrDash(value) {
    return Number(value ?? 0) === 0 ? '-' : rupiah(value);
}
