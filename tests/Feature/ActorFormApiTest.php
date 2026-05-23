<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\SubPengeluaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActorFormApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_operator_only_receives_staff_operator_forms(): void
    {
        $this->ensureCategory('IV', 'Pembelian Pupuk & Racun', 'Pupuk Urea', 'pupuk');

        $user = $this->userWithRole(User::ROLE_STAFF_OPERATOR);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/aktor/forms');

        $response->assertOk()
            ->assertJsonPath('actor.role', User::ROLE_STAFF_OPERATOR);

        $slugs = collect($response->json('menus'))->pluck('slug');

        $this->assertTrue($slugs->contains('pembelian-pupuk'));
        $this->assertTrue($slugs->contains('perlengkapan'));
        $this->assertTrue($slugs->contains('biaya-umum'));
        $this->assertTrue($slugs->contains('insentive'));
        $this->assertTrue($slugs->contains('rekap-laporan-keuangan'));
        $this->assertFalse($slugs->contains('biaya-produksi'));

        $this->getJson('/api/aktor/forms/biaya-produksi')->assertForbidden();
        $this->getJson('/api/aktor/forms/pembelian-pupuk')->assertOk();
    }

    public function test_mandor_can_submit_allowed_expense_form_to_api(): void
    {
        $sub = $this->ensureCategory('VI', 'Pemakaian Alat Berat', 'Traktor Test', 'alat_berat');
        $user = $this->userWithRole(User::ROLE_MANDOR);

        Sanctum::actingAs($user);

        $payload = [
            'tanggal' => '2099-05-16',
            'jumlah' => 250000,
            'jenis_transaksi' => 'kredit',
            'blok' => 'A1',
            'volume' => 5,
            'satuan' => 'HM',
            'harga_satuan' => 50000,
            'supplier_vendor' => 'Operator Alat',
            'no_referensi' => 'AB-2099-001',
            'sudah_bayar' => true,
        ];

        $response = $this->postJson("/api/aktor/forms/pemakaian-alat-berat/{$sub->id}", $payload);

        $response->assertCreated()
            ->assertJsonPath('data.sub_id', $sub->id)
            ->assertJsonPath('data.kategori_id', $sub->kategori_id);

        $this->assertDatabaseHas('pengeluaran', [
            'kategori_id' => $sub->kategori_id,
            'sub_id' => $sub->id,
            'jumlah' => 250000,
            'kredit' => 250000,
            'saldo' => -250000,
        ]);

        $this->assertDatabaseHas('pengeluaran_alat_berat', [
            'supplier_vendor' => 'Operator Alat',
            'no_referensi' => 'AB-2099-001',
        ]);
    }

    public function test_staff_operator_cannot_submit_mandor_form(): void
    {
        $sub = $this->ensureCategory('VI', 'Pemakaian Alat Berat', 'Grader Test', 'alat_berat');
        $user = $this->userWithRole(User::ROLE_STAFF_OPERATOR);

        Sanctum::actingAs($user);

        $this->postJson("/api/aktor/forms/pemakaian-alat-berat/{$sub->id}", [
            'tanggal' => '2099-05-16',
            'jumlah' => 100000,
            'jenis_transaksi' => 'kredit',
        ])->assertForbidden();
    }

    private function ensureCategory(string $nomor, string $nama, string $subName, string $detailType): SubPengeluaran
    {
        $kategori = KategoriPengeluaran::query()
            ->where('nomor_kategori', $nomor)
            ->first();

        if (!$kategori) {
            $kategori = KategoriPengeluaran::create([
                'nomor_kategori' => $nomor,
                'nama_kategori' => $nama,
                'urutan' => 900,
            ]);
        }

        return SubPengeluaran::query()
            ->where('kategori_id', $kategori->id)
            ->where('nama_sub', $subName)
            ->first()
            ?: SubPengeluaran::create([
                'kategori_id' => $kategori->id,
                'nomor_sub' => 99,
                'nama_sub' => $subName,
                'jenis_detail' => $detailType,
            ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }
}
