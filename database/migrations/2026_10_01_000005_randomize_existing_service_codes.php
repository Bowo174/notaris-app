<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $services = DB::table('services')->orderBy('id')->get(['id', 'code']);
        $ids = [];

        foreach ($services as $service) {
            if (preg_match('/^(PPAT|NOTARIS)-\d{5}$/', $service->code)) {
                $ids[] = $service->id;
                DB::table('services')->where('id', $service->id)->update(['code' => '__SERVICE_CODE_TMP_'.$service->id]);
            }
        }

        foreach ($ids as $id) {
            $service = DB::table('services')->where('id', $id)->first(['service_type']);
            $prefix = $service->service_type === 'PPAT' ? 'PPAT' : 'NOTARIS';

            do {
                $code = $prefix.'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (DB::table('services')->where('code', $code)->exists());

            DB::table('services')->where('id', $id)->update(['code' => $code]);
        }
    }

    public function down(): void
    {
        $services = DB::table('services')->whereIn('service_type', ['Notaris', 'PPAT'])->orderBy('id')->get(['id', 'service_type']);
        foreach ($services as $service) {
            DB::table('services')->where('id', $service->id)->update(['code' => '__SERVICE_CODE_TMP_'.$service->id]);
        }

        $sequences = ['Notaris' => 0, 'PPAT' => 0];
        foreach ($services as $service) {
            $prefix = $service->service_type === 'PPAT' ? 'PPAT' : 'NOTARIS';
            $sequences[$service->service_type]++;
            $code = $prefix.'-'.str_pad((string) $sequences[$service->service_type], 5, '0', STR_PAD_LEFT);
            DB::table('services')->where('id', $service->id)->update(['code' => $code]);
        }
    }
};
