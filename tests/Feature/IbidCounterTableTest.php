<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IbidCounterTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_ibid_counter_punya_tepat_satu_baris_setelah_migrasi(): void
    {
        $this->assertSame(1, DB::table('ibid_counter')->count());
        $this->assertSame(0, DB::table('ibid_counter')->value('nomor_terakhir'));
    }
}
