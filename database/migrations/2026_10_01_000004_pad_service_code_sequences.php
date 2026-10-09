<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $services = DB::table('services')->orderBy('id')->get(['id', 'code']);
        $codes = [];

        foreach ($services as $service) {
            if (preg_match('/^(PPAT|NOTARIS)-(\d+)$/', $service->code, $matches)) {
                $codes[$service->id] = $matches[1].'-'.str_pad($matches[2], 5, '0', STR_PAD_LEFT);
                DB::table('services')->where('id', $service->id)->update(['code' => '__SERVICE_CODE_TMP_'.$service->id]);
            }
        }

        foreach ($codes as $id => $code) {
            DB::table('services')->where('id', $id)->update(['code' => $code]);
        }
    }

    public function down(): void
    {
        $services = DB::table('services')->orderBy('id')->get(['id', 'code']);
        $codes = [];

        foreach ($services as $service) {
            if (preg_match('/^(PPAT|NOTARIS)-(\d{5})$/', $service->code, $matches)) {
                $codes[$service->id] = $matches[1].'-'.(int) $matches[2];
                DB::table('services')->where('id', $service->id)->update(['code' => '__SERVICE_CODE_TMP_'.$service->id]);
            }
        }

        foreach ($codes as $id => $code) {
            DB::table('services')->where('id', $id)->update(['code' => $code]);
        }
    }
};
