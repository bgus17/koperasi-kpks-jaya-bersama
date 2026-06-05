<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RekapKeuanganReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_realtime_rekap_report(): void
    {
        $tahun = 2097;
        $this->seedReportData($tahun);

        $response = $this->actingAs($this->adminUser())
            ->get(route('rekap.index', ['tahun' => $tahun]));

        $response->assertOk();
        $response->assertSee('Dana Kebun Tahun ' . $tahun);
        $response->assertSee('Grand Total');
        $response->assertSee('BIAYA LAPORAN');
        $response->assertSee('Rp 400.000');
        $response->assertSee('Rp 150.000');
    }

    public function test_admin_can_download_rekap_pdf_and_excel(): void
    {
        $tahun = 2096;
        $this->seedReportData($tahun);
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('rekap.export.pdf', ['tahun' => $tahun]))
            ->assertOk()
            ->assertDownload("rekap-keuangan-{$tahun}.pdf");

        $this->actingAs($user)
            ->get(route('rekap.export.excel', ['tahun' => $tahun]))
            ->assertOk()
            ->assertDownload("rekap-keuangan-{$tahun}.xlsx");
    }

    private function seedReportData(int $tahun): void
    {
        Pendapatan::create([
            'nomor_kategori' => 'I',
            'kategori' => 'Pendapatan Diterima',
            'sub_kategori' => 'Pendapatan Diterima',
            'debet' => 400_000,
            'kredit' => 0,
            'saldo' => 400_000,
            'tahun' => $tahun,
            'tanggal' => "{$tahun}-01-10",
        ]);

        $kategori = KategoriPengeluaran::create([
            'nomor_kategori' => 'RPT',
            'nama_kategori' => 'Biaya Laporan',
            'urutan' => 1001,
        ]);

        $sub = SubPengeluaran::create([
            'kategori_id' => $kategori->id,
            'nomor_sub' => 1,
            'nama_sub' => 'Administrasi Laporan',
            'jenis_detail' => 'umum',
        ]);

        Pengeluaran::create([
            'kategori_id' => $kategori->id,
            'sub_id' => $sub->id,
            'tanggal' => "{$tahun}-01-15",
            'jumlah' => 150_000,
            'jenis_transaksi' => 'kredit',
            'debet' => 0,
            'kredit' => 150_000,
            'saldo' => -150_000,
            'keterangan' => null,
            'sudah_bayar' => true,
        ]);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Role::findOrCreate(User::ROLE_ADMIN, 'web');
        $user->assignRole(User::ROLE_ADMIN);

        return $user;
    }
}
