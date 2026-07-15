<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Services\Erp\Drivers\PerfexFlow1OwnProductsDriver;
use App\Services\Erp\Drivers\PerfexFlow2DropshipImportDriver;
use App\Services\Erp\Drivers\PerfexFlow3SupplierExportDriver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ErpAbilitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->upsert([
            'key'          => 'erp-flow1-own-products',
            'name'         => 'ERP Export - Own Products (Perfex)',
            'type'         => 'export',
            'driver_class' => PerfexFlow1OwnProductsDriver::class,
            'description'  => 'Rocket LMS -> ERP -> Accounting. Apna khud ka product/booking bech rahe ho, uska data Perfex ERP mein jaata hai.',
            'fields' => [
                ['key' => 'api_base_url', 'label' => 'ERP API Base URL', 'type' => 'text', 'required' => true],
                ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
            ],
        ]);

        $this->upsert([
            'key'          => 'erp-flow2-dropship-import',
            'name'         => 'ERP Import - Dropshipping (Perfex)',
            'type'         => 'dropshipping',
            'driver_class' => PerfexFlow2DropshipImportDriver::class,
            'description'  => 'Supplier/ERP Feed -> ERP -> Rocket LMS. Doosre producers ke products/inventory ko import karke apne Rocket LMS mein dikhana.',
            'fields' => [
                ['key' => 'api_base_url', 'label' => 'ERP API Base URL', 'type' => 'text', 'required' => true],
                ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
                ['key' => 'import_dropshipping_enabled', 'label' => 'Enable Dropshipping Import', 'type' => 'boolean', 'required' => false],
            ],
        ]);

        $this->upsert([
            'key'          => 'erp-flow3-supplier-export',
            'name'         => 'ERP Export - Supplier Feed (Perfex)',
            'type'         => 'export',
            'driver_class' => PerfexFlow3SupplierExportDriver::class,
            'description'  => 'Rocket LMS -> ERP -> External Marketplace. Customer apna data doosre marketplace ko as a supplier bhejta hai.',
            'fields' => [
                ['key' => 'api_base_url', 'label' => 'ERP API Base URL', 'type' => 'text', 'required' => true],
                ['key' => 'api_key', 'label' => 'Supplier Feed API Key', 'type' => 'password', 'required' => true],
                ['key' => 'webhook_secret', 'label' => 'Webhook Secret (optional)', 'type' => 'text', 'required' => false],
            ],
        ]);
    }

    protected function upsert(array $data): void
    {
        Ability::updateOrCreate(
            ['key' => $data['key']],
            [
                'name'         => $data['name'],
                'type'         => $data['type'],
                'driver_class' => $data['driver_class'],
                'description'  => $data['description'],
                'schema_json'  => ['fields' => $data['fields']],
                'is_active'    => true,
            ]
        );
    }
}
