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
            $table->dropForeign(['service_id']);
            $table->foreignId('service_id')->nullable()->change();
            $table->string('service_code_snapshot', 50)->nullable()->change();
            $table->string('other_service')->nullable()->after('service_name_snapshot');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('orders')->whereNull('service_id')->orWhereNull('service_code_snapshot')->exists()) {
            throw new RuntimeException('Custom-service orders exist; they must be reassigned before this migration can be rolled back.');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('other_service');
            $table->foreignId('service_id')->nullable(false)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('service_code_snapshot', 50)->nullable(false)->change();
        });
    }
};
