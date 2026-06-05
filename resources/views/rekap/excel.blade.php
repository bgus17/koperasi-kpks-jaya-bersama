<table>
    <thead>
        <tr>
            <th colspan="5">{{ strtoupper($laporan['periode']['judul']) }}</th>
        </tr>
        <tr>
            <th colspan="5">{{ strtoupper($laporan['periode']['subjudul']) }}</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Keterangan</th>
            <th>Debet</th>
            <th>Kredit</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($laporan['sections'] as $section)
            <tr>
                <td>{{ $section['nomor'] }}</td>
                <td>{{ strtoupper($section['nama']) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @forelse($section['rows'] as $row)
                <tr>
                    <td>{{ $row['nomor'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                    <td>{{ $row['debet'] ?: null }}</td>
                    <td>{{ $row['kredit'] ?: null }}</td>
                    <td>{{ $row['has_data'] ? $row['saldo'] : null }}</td>
                </tr>
            @empty
                <tr>
                    <td></td>
                    <td>Belum ada data.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforelse
        @endforeach
        <tr>
            <td>{{ $laporan['periode']['tanggal_tutup']->format('d-M-y') }}</td>
            <td>Grand Total</td>
            <td>{{ $laporan['summary']['total_debet'] }}</td>
            <td>{{ $laporan['summary']['total_kredit'] }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>Saldo</td>
            <td></td>
            <td></td>
            <td>{{ $laporan['summary']['saldo_akhir'] }}</td>
        </tr>
    </tbody>
</table>
