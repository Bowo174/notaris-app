<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['service_type' => 'Notaris', 'name' => 'FIDUSIA', 'base_price' => 5000000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'WASIAT', 'base_price' => 3500000, 'estimated_duration' => 5, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'PT', 'base_price' => 3000000, 'estimated_duration' => 5, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'YAYASAN', 'base_price' => 4000000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'PERKUMPULAN', 'base_price' => 4000000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'CV', 'base_price' => 750000, 'estimated_duration' => 2, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'APOSTILE', 'base_price' => 2500000, 'estimated_duration' => 4, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'KOPERASI', 'base_price' => 3000000, 'estimated_duration' => 5, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'LEGALISASI', 'base_price' => 2000000, 'estimated_duration' => 3, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'WARMERKING', 'base_price' => 350000, 'estimated_duration' => 1, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'FIRMA', 'base_price' => 350000, 'estimated_duration' => 1, 'estimate_unit' => 'hari'],
            ['service_type' => 'Notaris', 'name' => 'PERSEKUTUAN PERDATA', 'base_price' => 350000, 'estimated_duration' => 1, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'AJB', 'base_price' => 4500000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'ATM', 'base_price' => 3500000, 'estimated_duration' => 6, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'HIBAH', 'base_price' => 4000000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'APDP', 'base_price' => 3500000, 'estimated_duration' => 6, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'APHB', 'base_price' => 2500000, 'estimated_duration' => 4, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'APHT', 'base_price' => 4000000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'APHGB', 'base_price' => 4000000, 'estimated_duration' => 7, 'estimate_unit' => 'hari'],
            ['service_type' => 'PPAT', 'name' => 'SKMHT', 'base_price' => 500000, 'estimated_duration' => 2, 'estimate_unit' => 'hari'],

        ];

        foreach ($services as $service) {
            $existing = Service::query()
                ->where('service_type', $service['service_type'])
                ->where('name', $service['name'])
                ->first();

            if ($existing) {
                $existing->update($service);
                continue;
            }

            $service['code'] = $this->generateCode($service['service_type']);
            Service::create($service);
        }
    }

    private function generateCode(string $serviceType): string
    {
        $prefix = $serviceType === 'PPAT' ? 'PPAT' : 'NOTARIS';

        do {
            $code = $prefix.'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Service::query()->where('code', $code)->exists());

        return $code;
    }
}
