<?php

namespace App\Imports;

use App\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StaffsImport implements ToModel, WithHeadingRow
{
    protected $errors = [];

    public function model(array $row)
    {
        // Log the row for debugging
        Log::info('Processing row:', $row);

      // Check required fields
        $requiredFields = ['status'];
        foreach ($requiredFields as $field) 
        {
            if (!array_key_exists($field, $row) || (empty($row[$field]) && $row[$field] !== '0')) {
                $this->errors[] = "Row skipped: Missing or empty field '{$field}'";
                Log::warning("Skipping row due to missing or empty field: {$field}", $row);
                return null;
            }
        }
        // Validation rules (aligned with log data and previous versions)
        $validator = Validator::make($row, [
            'full_name' => 'nullable|min:3|max:128',
            'mobile' => 'nullable|numeric|unique:users,mobile',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'backend_link' => 'nullable|url|max:255',
            'back_iframe_height' => 'nullable|integer|min:100',
            'frontend_link' => 'nullable|url|max:255',
            'front_iframe_height' => 'nullable|integer|min:100',
            'bio' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'facebook_id' => 'nullable|string|max:255',
            'remember_token' => 'nullable|string|max:100',
            'logged_count' => 'nullable|integer|min:0',
            'verified' => 'nullable|in:0,1,true,false',
            'financial_approval' => 'nullable|in:0,1,true,false',
            'installment_approval' => 'nullable|in:0,1,true,false',
            'enable_installments' => 'nullable|in:0,1,true,false',
            'disable_cashback' => 'nullable|in:0,1,true,false',
            'enable_registration_bonus' => 'nullable|in:0,1,true,false',
            'registration_bonus_amount' => 'nullable|numeric|min:0',
            'cover_img' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'address' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'location' => 'nullable|string',
            'status' => 'required|in:active,pending,inactive',
            'access_content' => 'nullable|in:0,1,true,false',
            'enable_ai_content' => 'nullable|in:0,1,true,false',
            'language' => 'nullable|string|max:64',
            'currency' => 'nullable|string|max:64',
            'timezone' => 'nullable|string|max:64',
            'newsletter' => 'nullable|in:0,1,true,false',
            'public_message' => 'nullable|in:0,1,true,false',
            'identity_scan' => 'nullable|string',
            'certificate' => 'nullable|string',
            'affiliate' => 'nullable|in:0,1,true,false',
            'can_create_store' => 'nullable|in:0,1,true,false',
            'ban' => 'nullable|in:0,1,true,false',
            'ban_start_at' => 'nullable|integer',
            'ban_end_at' => 'nullable|integer',
            'offline' => 'nullable|in:0,1,true,false',
            'offline_message' => 'nullable|string',
        ]);
        // +++++++++++++++++ Validation Errors +++++++++++++++++
        if ($validator->fails()) 
        {
            $errorMessages = $validator->errors()->all();
            foreach ($errorMessages as $error) {
                $this->errors[] = $error; // Store raw error message
            }
            Log::error('Validation failed for row:', [
                'errors' => $errorMessages,
                'row' => $row
            ]);
            return null;
        }
        $data = $row;
        // Prepare data for user creation
        $userData = [
            'full_name' => $data['full_name'],
            'role_name' => 'education',
            'role_id' => 6,
            // $usernameField => $usernameValue,
            'email'  => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'backend_link' => $data['backend_link'] ?? null,
            'back_iframe_height' => $data['back_iframe_height'] ?? null,
            'frontend_link' => $data['frontend_link'] ?? null,
            'front_iframe_height' => $data['front_iframe_height'] ?? null,
            'bio' => $data['bio'] ?? null,
            'password' => User::generatePassword($data['password']),
            'facebook_id' => $data['facebook_id'] ?? null,
            'remember_token' => $data['remember_token'] ?? null,
            'logged_count' => $data['logged_count'] ?? 0,
            'meeting_type' => "all",
            'verified' => filter_var($data['verified'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'financial_approval' => filter_var($data['financial_approval'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'installment_approval' => filter_var($data['installment_approval'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'enable_installments' => filter_var($data['enable_installments'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'disable_cashback' => filter_var($data['disable_cashback'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'enable_registration_bonus' => filter_var($data['enable_registration_bonus'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'registration_bonus_amount' => $data['registration_bonus_amount'] ?? null,
            'cover_img' => $data['cover_img'] ?? null,
            'headline' => $data['headline'] ?? null,
            'about' => $data['about'] ?? null,
            'address' => $data['address'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'province_id' => $data['province_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'location' => $data['location'] ?? null,
            'meeting_type' => "all",
            'status' => $data['status'],
            'access_content' => filter_var($data['access_content'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'enable_ai_content' => filter_var($data['enable_ai_content'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'language' => $data['language'] ?? null,
            'currency' => $data['currency'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'newsletter' => filter_var($data['newsletter'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'public_message' => filter_var($data['public_message'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'identity_scan' => $data['identity_scan'] ?? null,
            'certificate' => $data['certificate'] ?? null,
            'affiliate' => filter_var($data['affiliate'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'can_create_store' => filter_var($data['can_create_store'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'ban' => filter_var($data['ban'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'ban_start_at' => $data['ban_start_at'] ?? null,
            'ban_end_at' => $data['ban_end_at'] ?? null,
            'offline' => filter_var($data['offline'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'offline_message' => $data['offline_message'] ?? null,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        // Create the staff user
        try {
            Log::info('Attempting to create staff with data:', $userData);
            $staff = User::create($userData);
            if ($staff) {
                Log::info("Staff created successfully: ID {$staff->id}", $row);
                return $staff;
            } else {
                $this->errors[] = "System: Failed to create staff: User::create returned null";
                Log::error('Failed to create staff: User::create returned null', $row);
                return null;
            }
        } catch (\Exception $e) {
            $this->errors[] = "Failed to create staff: {$e->getMessage()}";
            Log::error('Failed to create staff due to exception:', [
                'message' => $e->getMessage(),
                'row' => $row
            ]);
            return null;
        }
    }

    /**
     * Check if there are any errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Get the collected errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}