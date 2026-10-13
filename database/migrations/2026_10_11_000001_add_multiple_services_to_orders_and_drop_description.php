<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('service_code_snapshot', 50)->nullable();
            $table->string('service_name_snapshot');
            $table->timestamps();
            $table->unique(['order_id', 'service_id']);
        });

        DB::table('orders')
            ->join('services', 'services.id', '=', 'orders.service_id')
            ->select([
                'orders.id as order_id',
                'services.id as service_id',
                'services.code',
                'services.name',
            ])
            ->orderBy('orders.id')
            ->each(function ($row) {
                DB::table('order_services')->insert([
                    'order_id' => $row->order_id,
                    'service_id' => $row->service_id,
                    'service_code_snapshot' => $row->code,
                    'service_name_snapshot' => $row->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        Schema::dropIfExists('order_services');
    }
};
