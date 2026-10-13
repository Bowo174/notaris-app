<?php

namespace App\Services;

class WorkSheetCodeGenerator
{
    public function generate(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $suffix = '';

        for ($index = 0; $index < 5; $index++) {
            $suffix .= $letters[random_int(0, strlen($letters) - 1)];
        }

        return 'PHB-'.now()->format('dmY').'-'.$suffix;
    }
}
