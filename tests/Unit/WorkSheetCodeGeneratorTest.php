<?php

namespace Tests\Unit;

use App\Services\WorkSheetCodeGenerator;
use Tests\TestCase;

class WorkSheetCodeGeneratorTest extends TestCase
{
    public function test_it_generates_a_phb_code_with_the_current_date_and_five_random_letters(): void
    {
        $code = app(WorkSheetCodeGenerator::class)->generate();

        $this->assertMatchesRegularExpression('/^PHB-\d{8}-[A-Z]{5}$/', $code);
        $this->assertSame(now()->format('dmY'), substr($code, 4, 8));
    }
}
