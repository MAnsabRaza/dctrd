<?php

namespace Database\Seeders;

use App\Models\RoleCatalog;
use Illuminate\Database\Seeder;

class RoleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // ── Instructor family ──────────────────────────────
            ['family' => 'instructor', 'key' => 'instructor',       'label' => 'Instructor',        'supersedes' => []],
            ['family' => 'instructor', 'key' => 'seller',           'label' => 'Seller',             'supersedes' => []],
            ['family' => 'instructor', 'key' => 'operator',         'label' => 'Operator (Tour)',    'supersedes' => []],
            ['family' => 'instructor', 'key' => 'event_organizer',  'label' => 'Event Organizer',    'supersedes' => []],
            ['family' => 'instructor', 'key' => 'agent',            'label' => 'Agent',              'supersedes' => []],

            // ── Organization family ─────────────────────────────
            // Organization apni team mein Instructor + Student dono cover karta hai (superset)
            ['family' => 'organization', 'key' => 'businesses_seller',   'label' => 'Businesses Seller',   'supersedes' => ['instructor', 'student']],
            ['family' => 'organization', 'key' => 'businesses_producer', 'label' => 'Businesses Producer', 'supersedes' => ['instructor', 'student']],
            ['family' => 'organization', 'key' => 'businesses_services', 'label' => 'Businesses Services', 'supersedes' => ['instructor', 'student']],
            ['family' => 'organization', 'key' => 'tour_operator_org',   'label' => 'Tour Operator',        'supersedes' => ['instructor', 'student', 'operator']],
            ['family' => 'organization', 'key' => 'agency_ota',          'label' => 'Agency / OTA',         'supersedes' => ['instructor', 'student', 'agent']],

            // ── Customer family ──────────────────────────────────
            ['family' => 'customer', 'key' => 'individual',         'label' => 'Individual',           'supersedes' => []],
            ['family' => 'customer', 'key' => 'student',            'label' => 'Student',              'supersedes' => []],
            ['family' => 'customer', 'key' => 'store',              'label' => 'Store',                'supersedes' => []],
            ['family' => 'customer', 'key' => 'wholeseller',        'label' => 'Wholeseller',          'supersedes' => []],
            ['family' => 'customer', 'key' => 'importer',           'label' => 'Importer',             'supersedes' => []],
            ['family' => 'customer', 'key' => 'advertiser',         'label' => 'Advertiser',           'supersedes' => []],
            ['family' => 'customer', 'key' => 'promoter',           'label' => 'Promoter',             'supersedes' => []],
            ['family' => 'customer', 'key' => 'travel_agency_ota',  'label' => 'Travel Agency / OTA',  'supersedes' => []],
            ['family' => 'customer', 'key' => 'tour_operator_cust', 'label' => 'Tour Operator',        'supersedes' => []],
        ];

        foreach ($roles as $index => $role) {
            RoleCatalog::updateOrCreate(
                ['key' => $role['key']],
                [
                    'family'     => $role['family'],
                    'label'      => $role['label'],
                    'supersedes' => $role['supersedes'],
                    'sort_order' => $index,
                    'active'     => true,
                ]
            );
        }
    }
}