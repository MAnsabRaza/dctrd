<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckoutModulesSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────
        // Default Checkout Modules
        // ─────────────────────────────────────────
        $modules = [

            // 1. Days (Date Range Picker)
            [
                'name'        => 'days',
                'input_type'  => 'date_range',
                'config'      => json_encode([
                    'min_days' => 1,
                    'max_days' => 365,
                ]),
                'price_rule'  => json_encode([
                    'type'   => 'per_day',
                    'amount' => 15, // TEST VALUE — admin panel se change kar sakte ho
                ]),
                'order_index' => 1,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 2. Hours (Time Slot Selector)
            [
                'name'        => 'hours',
                'input_type'  => 'time_slot',
                'config'      => json_encode([
                    'slot_duration' => 60, // minutes
                    'slots' => [
                        '09:00', '10:00', '11:00',
                        '12:00', '13:00', '14:00',
                        '15:00', '16:00', '17:00',
                    ],
                ]),
                'price_rule'  => json_encode([
                    'type'   => 'per_hour',
                    'amount' => 8, // TEST VALUE
                ]),
                'order_index' => 2,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 3. Staff Member (Dropdown)
            [
                'name'        => 'staff_member',
                'input_type'  => 'select',
                'config'      => json_encode([
                    'source' => 'org_staff',
                ]),
                'price_rule'  => json_encode([
                    'type' => 'none',
                ]),
                'order_index' => 3,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 4. Persons + Children (Quantity Steppers)
            [
                'name'        => 'persons_children',
                'input_type'  => 'stepper',
                'config'      => json_encode([
                    'adults'   => ['min' => 1, 'max' => 20, 'price' => 10],
                    'children' => ['min' => 0, 'max' => 10, 'price' => 5],
                    'rooms'    => ['min' => 1, 'max' => 10, 'price' => 0],
                ]),
                'price_rule'  => json_encode([
                    'type' => 'per_person',
                ]),
                'order_index' => 4,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 5. Extra Services (Checkbox List with Prices)
            [
                'name'        => 'extra_services',
                'input_type'  => 'checkbox_list',
                'config'      => json_encode([
                    'options' => [
                        ['id' => 1, 'label' => 'Breakfast',     'price' => 10],
                        ['id' => 2, 'label' => 'Transfer',      'price' => 20],
                        ['id' => 3, 'label' => 'Late Check-out','price' => 15],
                        ['id' => 4, 'label' => 'Airport Pickup','price' => 25],
                    ],
                ]),
                'price_rule'  => json_encode([
                    'type' => 'additive',
                ]),
                'order_index' => 5,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 6. Cancellation Policy (Info + Agree Checkbox)
            [
                'name'        => 'cancellation_policy',
                'input_type'  => 'info_checkbox',
                'config'      => json_encode([
                    'policy_text' =>
                        'Free cancellation up to 24 hours before check-in. ' .
                        'After that, the first night is non-refundable.',
                ]),
                'price_rule'  => json_encode([
                    'type' => 'none',
                ]),
                'order_index' => 6,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 7. Message for Checkout (Customer Instructions)
            [
                'name'        => 'checkout_message',
                'input_type'  => 'textarea',
                'config'      => json_encode([
                    'placeholder' => 'Any special instructions or requests?',
                    'max_length'  => 500,
                    'rows'        => 4,
                ]),
                'price_rule'  => json_encode([
                    'type' => 'none',
                ]),
                'order_index' => 7,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            // 8. Message to Reviewer (Note for Instructor/Org)
            [
                'name'        => 'reviewer_message',
                'input_type'  => 'textarea',
                'config'      => json_encode([
                    'placeholder' => 'Message to instructor or organizer',
                    'max_length'  => 500,
                    'rows'        => 4,
                ]),
                'price_rule'  => json_encode([
                    'type' => 'none',
                ]),
                'order_index' => 8,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        // Insert modules
        DB::table('checkout_modules')->insertOrIgnore($modules);

        // ─────────────────────────────────────────
        // Default English Translations
        // ─────────────────────────────────────────
        $translations = [
            [
                'module_name' => 'days',
                'locale'      => 'en',
                'label'       => 'Select Dates',
                'help_text'   => 'Choose your check-in and check-out dates',
            ],
            [
                'module_name' => 'hours',
                'locale'      => 'en',
                'label'       => 'Select Time Slot',
                'help_text'   => 'Choose your preferred time slot',
            ],
            [
                'module_name' => 'staff_member',
                'locale'      => 'en',
                'label'       => 'Select Staff Member',
                'help_text'   => 'Choose your preferred staff member',
            ],
            [
                'module_name' => 'persons_children',
                'locale'      => 'en',
                'label'       => 'Guests',
                'help_text'   => 'Select number of adults, children and rooms',
            ],
            [
                'module_name' => 'extra_services',
                'locale'      => 'en',
                'label'       => 'Extra Services',
                'help_text'   => 'Select any additional services you require',
            ],
            [
                'module_name' => 'cancellation_policy',
                'locale'      => 'en',
                'label'       => 'Cancellation Policy',
                'help_text'   => 'Please read and agree to our cancellation policy',
            ],
            [
                'module_name' => 'checkout_message',
                'locale'      => 'en',
                'label'       => 'Message for Check-out',
                'help_text'   => 'Any special instructions for your booking',
            ],
            [
                'module_name' => 'reviewer_message',
                'locale'      => 'en',
                'label'       => 'Message to Reviewer',
                'help_text'   => 'Private message to the instructor or organizer',
            ],
        ];

        foreach ($translations as $translation) {
            $module = DB::table('checkout_modules')
                        ->where('name', $translation['module_name'])
                        ->first();

            if ($module) {
                DB::table('checkout_module_translations')
                  ->insertOrIgnore([
                      'module_id'  => $module->id,
                      'locale'     => $translation['locale'],
                      'label'      => $translation['label'],
                      'help_text'  => $translation['help_text'],
                      'created_at' => now(),
                      'updated_at' => now(),
                  ]);
            }
        }

        $this->command->info('✅ Checkout modules seeded successfully!');
        $this->command->info('   Modules created: ' . count($modules));
        $this->command->info('   Translations created: ' . count($translations));
    }
}