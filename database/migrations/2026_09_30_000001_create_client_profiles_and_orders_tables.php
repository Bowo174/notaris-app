<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik', 32)->nullable()->index();
            $table->string('client_type', 32);
            $table->string('represented_party_name')->nullable();
            $table->string('phone', 24);
            $table->string('email')->nullable();
            $table->text('address');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('client_name_snapshot');
            $table->string('client_nik_snapshot', 32)->nullable();
            $table->string('client_type_snapshot', 32);
            $table->string('represented_party_snapshot')->nullable();
            $table->string('client_phone_snapshot', 24);
            $table->string('client_email_snapshot')->nullable();
            $table->text('client_address_snapshot');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('object_location')->nullable();
            $table->text('related_parties')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('status', 40)->default('Menunggu ditinjau');
            $table->timestamps();
            $table->index(['service_id', 'status']);
        });

        Schema::create('order_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('original_name');
            $table->string('path')->unique();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('client_profiles');
    }
};
