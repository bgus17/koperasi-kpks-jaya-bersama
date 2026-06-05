@php
    $formatMoney = function ($value, bool $dashWhenZero = true): string {
        $amount = (int) $value;

        if ($dashWhenZero && $amount === 0) {
            return '-';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    };
@endphp

<div class="table-wrap ledger-table-wrap">
    <table class="ledger-table">
        <thead>
            <tr>
                <th class="ledger-no-col">No</th>
                <th>Keterangan</th>
                <th class="text-right">Debet</th>
                <th class="text-right">Kredit</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan['sections'] as $section)
                <tr class="ledger-section-row">
                    <td>{{ $section['nomor'] }}</td>
                    <td>{{ strtoupper($section['nama']) }}</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td></td>
                </tr>

                @forelse($section['rows'] as $row)
                    <tr>
                        <td class="text-center">{{ $row['nomor'] }}</td>
                        <td>{{ $row['keterangan'] }}</td>
                        <td class="text-right rp rp-debet">{{ $formatMoney($row['debet']) }}</td>
                        <td class="text-right rp rp-kredit">{{ $formatMoney($row['kredit']) }}</td>
                        <td class="text-right rp {{ $row['saldo'] < 0 ? 'rp-kredit' : 'rp-saldo' }}">
                            {{ $row['has_data'] ? $formatMoney($row['saldo'], false) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td></td>
                        <td class="ledger-empty">Belum ada data pendapatan pada periode ini.</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                    </tr>
                @endforelse
            @endforeach
        </tbody>
        <tfoot>
            <tr class="ledger-total-row">
                <td>{{ $laporan['periode']['tanggal_tutup']->format('d-M-y') }}</td>
                <td>Grand Total</td>
                <td class="text-right rp">{{ $formatMoney($laporan['summary']['total_debet'], false) }}</td>
                <td class="text-right rp">{{ $formatMoney($laporan['summary']['total_kredit'], false) }}</td>
                <td></td>
            </tr>
            <tr class="ledger-balance-row">
                <td></td>
                <td>Saldo</td>
                <td></td>
                <td></td>
                <td class="text-right rp">{{ $formatMoney($laporan['summary']['saldo_akhir'], false) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
