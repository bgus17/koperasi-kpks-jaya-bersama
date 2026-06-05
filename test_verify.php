<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== REKAP TAHUN 2026 ===" . PHP_EOL;
$laporan = \App\Services\KeuanganLedgerService::laporan(2026, null);
$section = $laporan['sections'][0];
echo "Pendapatan rows: " . count($section['rows']) . PHP_EOL;
foreach ($section['rows'] as $row) {
    echo "  " . $row['nomor'] . ". " . $row['keterangan'] 
        . " | Debet: " . number_format($row['debet']) 
        . " | Kredit: " . number_format($row['kredit']) 
        . " | Saldo: " . number_format($row['saldo']) 
        . PHP_EOL;
}
echo PHP_EOL . "Summary:" . PHP_EOL;
echo "  Total Debet: " . number_format($laporan['summary']['total_debet']) . PHP_EOL;
echo "  Total Kredit: " . number_format($laporan['summary']['total_kredit']) . PHP_EOL;
echo "  Saldo Akhir: " . number_format($laporan['summary']['saldo_akhir']) . PHP_EOL;

echo PHP_EOL . "=== REKAP TAHUN 2025 (verifikasi tidak berubah) ===" . PHP_EOL;
$laporan25 = \App\Services\KeuanganLedgerService::laporan(2025, null);
$section25 = $laporan25['sections'][0];
echo "Pendapatan rows: " . count($section25['rows']) . PHP_EOL;
foreach ($section25['rows'] as $row) {
    echo "  " . $row['nomor'] . ". " . $row['keterangan']
        . " | Debet: " . number_format($row['debet'])
        . PHP_EOL;
}
