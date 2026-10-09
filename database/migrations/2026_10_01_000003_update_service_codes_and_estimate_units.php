<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('estimated_days', 'estimated_duration');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('estimate_unit', 10)->nullable()->after('estimated_duration');
        });

        DB::table('services')->whereNotNull('estimated_duration')->update(['estimate_unit' => 'hari']);

        $services = DB::table('services')->orderBy('id')->get(['id', 'service_type']);
        foreach ($services as $service) {
            DB::table('services')->where('id', $service->id)->update(['code' => '__SERVICE_CODE_TMP_'.$service->id]);
        }

        $sequences = ['Notaris' => 0, 'PPAT' => 0];
        foreach ($services as $service) {
            $prefix = $service->service_type === 'PPAT' ? 'PPAT' : 'NOTARIS';
            $type = $service->service_type === 'PPAT' ? 'PPAT' : 'Notaris';
            $sequences[$type]++;
            $code = $prefix.'-'.str_pad((string) $sequences[$type], 3, '0', STR_PAD_LEFT);

            DB::table('services')->where('id', $service->id)->update(['code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('estimate_unit');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('estimated_duration', 'estimated_days');
        });
    }
};
