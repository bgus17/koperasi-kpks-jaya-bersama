import { apiRequest } from '../core/http.js';
import { escapeHtml, qs, rupiah } from '../core/dom.js';

export function bindRekap(root) {
    const button = qs('[data-load-rekap]', root);

    if (!button) {
        return;
    }

    button.addEventListener('click', async () => {
        const year = qs('[data-rekap-year]', root).value || new Date().getFullYear();
        const target = qs('[data-rekap-result]', root);

        target.innerHTML = '<div class="notice">Memuat rekap...</div>';

        try {
            const response = await apiRequest(`/rekap/laporan-lengkap?tahun=${encodeURIComponent(year)}`);
            const ledger = response.ledger_summary ?? {};

            target.innerHTML = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Total Debet</th>
                            <th>Total Kredit</th>
                            <th>Saldo Akhir</th>
                            <th>Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${escapeHtml(response.tahun)}</td>
                            <td>${rupiah(ledger.total_debet)}</td>
                            <td>${rupiah(ledger.total_kredit)}</td>
                            <td>${rupiah(ledger.saldo_akhir)}</td>
                            <td>${escapeHtml(ledger.jumlah_record ?? '-')}</td>
                        </tr>
                    </tbody>
                </table>
            `;
        } catch (error) {
            target.innerHTML = `<div class="notice error">${escapeHtml(error.message)}</div>`;
        }
    });
}
