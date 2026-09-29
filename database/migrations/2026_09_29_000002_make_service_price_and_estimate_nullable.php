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
            $table->unsignedInteger('base_price')->nullable()->change();
            $table->unsignedInteger('estimated_days')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('services')->whereNull('base_price')->update(['base_price' => 0]);
        DB::table('services')->whereNull('estimated_days')->update(['estimated_days' => 1]);

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('base_price')->nullable(false)->change();
            $table->unsignedInteger('estimated_days')->nullable(false)->change();
        });
    }
};
