<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['code' => 'NOT-01', 'service_type' => 'Notaris', 'name' => 'FIDUSIA', 'base_price' => 5000000, 'estimated_days' => 7],
            ['code' => 'NOT-02', 'service_type' => 'Notaris', 'name' => 'WASIAT', 'base_price' => 3500000, 'estimated_days' => 5],
            ['code' => 'NOT-03', 'service_type' => 'Notaris', 'name' => 'PT', 'base_price' => 3000000, 'estimated_days' => 5],
            ['code' => 'NOT-04', 'service_type' => 'Notaris', 'name' => 'YAYASAN', 'base_price' => 4000000, 'estimated_days' => 7],
            ['code' => 'NOT-05', 'service_type' => 'Notaris', 'name' => 'PERKUMPULAN', 'base_price' => 4000000, 'estimated_days' => 7],
            ['code' => 'NOT-06', 'service_type' => 'Notaris', 'name' => 'CV', 'base_price' => 750000, 'estimated_days' => 2],
            ['code' => 'NOT-07', 'service_type' => 'Notaris', 'name' => 'APOSTILE', 'base_price' => 2500000, 'estimated_days' => 4],
            ['code' => 'NOT-08', 'service_type' => 'Notaris', 'name' => 'KOPERASI', 'base_price' => 3000000, 'estimated_days' => 5],
            ['code' => 'NOT-09', 'service_type' => 'Notaris', 'name' => 'Akta Hibah', 'base_price' => 2500000, 'estimated_days' => 4],
            ['code' => 'NOT-10', 'service_type' => 'Notaris', 'name' => 'LEGAISASI', 'base_price' => 2000000, 'estimated_days' => 3],
            ['code' => 'NOT-11', 'service_type' => 'Notaris', 'name' => 'WARMERKING', 'base_price' => 350000, 'estimated_days' => 1],
            ['code' => 'NOT-12', 'service_type' => 'Notaris', 'name' => 'LAYANAN LAINNYA', 'base_price' => null, 'estimated_days' => null],
            ['code' => 'PPAT-01', 'service_type' => 'PPAT', 'name' => 'AJB', 'base_price' => 4500000, 'estimated_days' => 7],
            ['code' => 'PPAT-02', 'service_type' => 'PPAT', 'name' => 'ATM', 'base_price' => 3500000, 'estimated_days' => 6],
            ['code' => 'PPAT-03', 'service_type' => 'PPAT', 'name' => 'HIBAH', 'base_price' => 4000000, 'estimated_days' => 7],
            ['code' => 'PPAT-04', 'service_type' => 'PPAT', 'name' => 'APDP', 'base_price' => 3500000, 'estimated_days' => 6],
            ['code' => 'PPAT-05', 'service_type' => 'PPAT', 'name' => 'APHB', 'base_price' => 2500000, 'estimated_days' => 4],
            ['code' => 'PPAT-06', 'service_type' => 'PPAT', 'name' => 'APHT', 'base_price' => 4000000, 'estimated_days' => 7],
            ['code' => 'PPAT-07', 'service_type' => 'PPAT', 'name' => 'APHGB', 'base_price' => 4000000, 'estimated_days' => 7],
            ['code' => 'PPAT-08', 'service_type' => 'PPAT', 'name' => 'SKMHT', 'base_price' => 500000, 'estimated_days' => 2],
            ['code' => 'PPAT-09', 'service_type' => 'PPAT', 'name' => 'LAYANAN LAINNYA', 'base_price' => null, 'estimated_days' => null]

        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['code' => $service['code']], $service);
        }
    }
}
