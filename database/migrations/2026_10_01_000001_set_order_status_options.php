<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 40)->default('Konsultasi')->change();
        });

        DB::table('orders')
            ->whereNotIn('status', ['Konsultasi', 'Dalam proses', 'Selesai'])
            ->update(['status' => 'Konsultasi']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 40)->default('Menunggu ditinjau')->change();
        });
    }
};
