<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->date('deed_date')->nullable();
            $table->string('deed_number', 100)->nullable();
            $table->string('deed_name')->nullable();
            $table->json('party_one')->nullable();
            $table->json('party_two')->nullable();
            $table->text('certificate_details')->nullable();
            $table->unsignedBigInteger('transaction_value')->nullable();
            $table->unsignedBigInteger('pbb_amount')->nullable();
            $table->unsignedBigInteger('bphtb_amount')->nullable();
            $table->unsignedBigInteger('pph_amount')->nullable();
            $table->timestamps();
        });

        Schema::create('work_sheet_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_sheet_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('label');
            $table->string('original_name');
            $table->string('path')->unique();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        DB::table('orders')
            ->leftJoin('services', 'services.id', '=', 'orders.service_id')
            ->where(fn ($query) => $query->where('services.service_type', 'PPAT')
                ->orWhere(fn ($custom) => $custom->whereNull('orders.service_id')->whereNotNull('orders.other_service')))
            ->select('orders.id')
            ->orderBy('orders.id')
            ->each(function ($order) {
                do {
                    $code = 'LK-'.now()->format('ymd').'-'.substr(strtoupper(bin2hex(random_bytes(4))), 0, 5);
                } while (DB::table('work_sheets')->where('code', $code)->exists());

                DB::table('work_sheets')->insert([
                    'order_id' => $order->id,
                    'code' => $code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_sheet_files');
        Schema::dropIfExists('work_sheets');
    }
};
