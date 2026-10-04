<?php

namespace Tests\Unit;

use App\Support\Terbilang;
use PHPUnit\Framework\TestCase;

class TerbilangTest extends TestCase
{
    public function test_it_converts_rupiah_values_to_indonesian_words(): void
    {
        $this->assertSame('nol', Terbilang::rupiah(0));
        $this->assertSame('sebelas', Terbilang::rupiah(11));
        $this->assertSame('dua belas', Terbilang::rupiah(12));
        $this->assertSame('seratus satu', Terbilang::rupiah(101));
        $this->assertSame('seribu', Terbilang::rupiah(1000));
        $this->assertSame('dua ratus tujuh puluh lima ribu', Terbilang::rupiah(275000));
        $this->assertSame('satu juta', Terbilang::rupiah(1000000));
    }
}
