<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Checkout Modules Translation
    |--------------------------------------------------------------------------
    */

    'name' => 'Name',
    'input_type' => 'Input Type',
    'config' => 'Config',
    'price_rule' => 'Price Rule',
    'list_title' => 'Checkout Modules',
    'create_title' => 'Create Checkout Module',
    'edit_title' => 'Edit Checkout Module',
    'order' => 'Order',
    'required' => 'Required',
    'yes' => 'Yes',
    'no' => 'No',

    'name_placeholder' => 'e.g. "Days", "Hours", "Staff Member"',
    'input_type_placeholder' => 'e.g. "text", "select", "date_range", "time_slot"',
    'config_hint' => 'JSON configuration for the module. Structure depends on the input type.',
    'price_rule_hint' => 'JSON defining how the module affects pricing. E.g. {"type": "per_day"}',

    'translations' => 'Translations',
    'label_placeholder' => 'Translated label for this module',
    'help_text_placeholder' => 'Translated help text for this module',

    // Validation Messages
    'name_already_exists' => 'A module with this name already exists.',
    'invalid_input_type' => 'The selected input type is invalid.',
    'invalid_json' => 'The JSON format is invalid.',

    // Success Messages
    'created_successfully' => 'Checkout module created successfully.',
    'updated_successfully' => 'Checkout module updated successfully.',
    'deleted_successfully' => 'Checkout module deleted successfully.',

    // Error Messages
    'create_failed' => 'Failed to create checkout module',
    'update_failed' => 'Failed to update checkout module',
    'delete_failed' => 'Failed to delete checkout module',

    'cannot_delete_in_use' => 'Module ":name" cannot be deleted because it is currently in use.',

    // Toggle Messages
    'module_activated' => 'Module activated successfully.',
    'module_deactivated' => 'Module deactivated successfully.',

    // Input Types
    'input_type_date_range' => 'Date Range',
    'input_type_time_slot' => 'Time Slot',
    'input_type_select' => 'Select',
    'input_type_stepper' => 'Stepper',
    'input_type_checkbox_list' => 'Checkbox List',
    'input_type_info_checkbox' => 'Info Checkbox',
    'input_type_textarea' => 'Textarea',

];