<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\SubPengeluaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_financial_dashboard(): void
    {
        $tahun = 2095;
        $this->seedDashboardData($tahun);

        $response = $this->actingAs($this->adminUser())
            ->get(route('dashboard', ['tahun' => $tahun]));

        $response->assertOk();
        $response->assertSee('Ringkasan Finansial Utama');
        $response->assertSee('Analisis Biaya Operasional');
        $response->assertSee('Rp 650.000');
        $response->assertSee('Biaya Panen');
    }

    public function test_admin_can_view_dashboard_when_period_only_has_expenses(): void
    {
        $tahun = 2094;
        $this->seedExpenseOnlyData($tahun);

        $response = $this->actingAs($this->adminUser())
            ->get(route('dashboard', ['tahun' => $tahun]));

        $response->assertOk();
        $response->assertSee('Ringkasan Finansial Utama');
        $response->assertSee('Biaya Operasional');
        $response->assertSee('Biaya Rawat Jalan');
    }

    private function seedDashboardData(int $tahun): void
    {
        Pendapatan::create([
            'nomor_kategori' => 'I',
            'kategori' => 'Pendapatan Diterima',
            'sub_kategori' => 'Pendapatan Diterima',
            'debet' => 1_000_000,
            'kredit' => 0,
            'saldo' => 1_000_000,
            'tahun' => $tahun,
            'tanggal' => "{$tahun}-01-10",
            'keterangan' => 'Pendapatan dashboard test',
        ]);

        $kategori = KategoriPengeluaran::create([
            'nomor_kategori' => 'II',
            'nama_kategori' => 'Biaya Produksi',
            'urutan' => 2,
        ]);

        $sub = SubPengeluaran::create([
            'kategori_id' => $kategori->id,
            'nomor_sub' => 1,
            'nama_sub' => 'Biaya Panen',
            'jenis_detail' => 'panen',
        ]);

        Pengeluaran::create([
            'kategori_id' => $kategori->id,
            'sub_id' => $sub->id,
            'tanggal' => "{$tahun}-01-12",
            'jumlah' => 350_000,
            'jenis_transaksi' => 'kredit',
            'debet' => 0,
            'kredit' => 350_000,
            'saldo' => -350_000,
            'keterangan' => 'Biaya panen dashboard test',
            'sudah_bayar' => true,
        ]);
    }

    private function seedExpenseOnlyData(int $tahun): void
    {
        $kategori = KategoriPengeluaran::create([
            'nomor_kategori' => 'III',
            'nama_kategori' => 'Biaya Perawatan',
            'urutan' => 3,
        ]);

        $sub = SubPengeluaran::create([
            'kategori_id' => $kategori->id,
            'nomor_sub' => 1,
            'nama_sub' => 'Biaya Rawat Jalan',
            'jenis_detail' => 'perawatan',
        ]);

        Pengeluaran::create([
            'kategori_id' => $kategori->id,
            'sub_id' => $sub->id,
            'tanggal' => "{$tahun}-03-15",
            'jumlah' => 125_000,
            'jenis_transaksi' => 'kredit',
            'debet' => 0,
            'kredit' => 125_000,
            'saldo' => -125_000,
            'keterangan' => 'Biaya perawatan tanpa pendapatan',
            'sudah_bayar' => false,
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
