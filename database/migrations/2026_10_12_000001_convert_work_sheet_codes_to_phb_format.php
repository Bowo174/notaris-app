<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_sheets', function (Blueprint $table) {
            $table->string('previous_code', 32)->nullable()->unique()->after('code');
        });

        DB::table('work_sheets')->orderBy('id')->get(['id', 'code', 'created_at'])->each(function ($sheet) {
            $oldCode = $sheet->code;
            $date = Carbon::parse($sheet->created_at)->format('dmY');
            do {
                $code = 'PHB-'.$date.'-'.$this->randomLetters();
            } while (DB::table('work_sheets')->where('code', $code)->orWhere('previous_code', $code)->exists());

            DB::table('work_sheets')->where('id', $sheet->id)->update([
                'code' => $code,
                'previous_code' => $oldCode,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('work_sheets')->whereNotNull('previous_code')->orderBy('id')->get(['id', 'previous_code'])->each(function ($sheet) {
            DB::table('work_sheets')->where('id', $sheet->id)->update(['code' => $sheet->previous_code]);
        });

        Schema::table('work_sheets', function (Blueprint $table) {
            $table->dropUnique(['previous_code']);
            $table->dropColumn('previous_code');
        });
    }

    private function randomLetters(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $suffix = '';

        for ($index = 0; $index < 5; $index++) {
            $suffix .= $letters[random_int(0, strlen($letters) - 1)];
        }

        return $suffix;
    }
};
