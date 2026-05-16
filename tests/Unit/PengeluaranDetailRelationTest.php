<?php

namespace Tests\Unit;

use App\Models\Pengeluaran;
use Tests\TestCase;

class PengeluaranDetailRelationTest extends TestCase
{
    public function test_remaining_card_profiles_use_their_own_detail_relations(): void
    {
        $this->assertSame('alatBeratDetail', Pengeluaran::detailRelationForProfile('alat_berat'));
        $this->assertSame('perlengkapanDetail', Pengeluaran::detailRelationForProfile('perlengkapan'));
        $this->assertSame('insentiveDetail', Pengeluaran::detailRelationForProfile('insentive'));

        $this->assertContains('alatBeratDetail', Pengeluaran::detailRelations());
        $this->assertContains('perlengkapanDetail', Pengeluaran::detailRelations());
        $this->assertContains('insentiveDetail', Pengeluaran::detailRelations());
    }

    public function test_category_numbers_resolve_to_separate_card_profiles(): void
    {
        $this->assertSame('alat_berat', Pengeluaran::resolveDetailProfile(null, 'VI'));
        $this->assertSame('perlengkapan', Pengeluaran::resolveDetailProfile(null, 'VII'));
        $this->assertSame('insentive', Pengeluaran::resolveDetailProfile(null, 'VIII'));
    }
}
