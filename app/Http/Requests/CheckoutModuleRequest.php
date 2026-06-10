<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutModuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $moduleData = $this->input('checkout_modules', []);
        $rules = [];

        // Har module ke liye validation
        foreach ($moduleData as $moduleName => $data) {
            switch ($moduleName) {

                case 'days':
                    $rules['checkout_modules.days.check_in'] = 'nullable|date|after_or_equal:today';
                    $rules['checkout_modules.days.check_out'] = 'nullable|date|after:checkout_modules.days.check_in';
                    break;

                case 'hours':
                    $rules['checkout_modules.hours'] = 'nullable|string|in:' . implode(',', [
                        '09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'
                    ]);
                    break;

                case 'staff_member':
                    $rules['checkout_modules.staff_member'] = 'nullable|integer|exists:users,id';
                    break;

                case 'persons_children':
                    $rules['checkout_modules.persons_children.adults'] = 'nullable|integer|min:1|max:20';
                    $rules['checkout_modules.persons_children.children'] = 'nullable|integer|min:0|max:10';
                    $rules['checkout_modules.persons_children.rooms'] = 'nullable|integer|min:1|max:10';
                    break;

                case 'extra_services':
                    $rules['checkout_modules.extra_services'] = 'nullable|array';
                    break;

                case 'cancellation_policy':
                    $rules['checkout_modules.cancellation_policy'] = 'nullable|accepted';
                    break;

                case 'checkout_message':
                    $rules['checkout_modules.checkout_message'] = 'nullable|string|max:500';
                    break;

                case 'reviewer_message':
                    $rules['checkout_modules.reviewer_message'] = 'nullable|string|max:500';
                    break;
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validation rules
     */
    public function messages()
    {
        return [
            'checkout_modules.days.check_in.date'              => trans('checkout.validation.check_in_invalid_date'),
            'checkout_modules.days.check_in.after_or_equal'    => trans('checkout.validation.check_in_past'),
            'checkout_modules.days.check_out.date'             => trans('checkout.validation.check_out_invalid_date'),
            'checkout_modules.days.check_out.after'            => trans('checkout.validation.check_out_before_check_in'),
            'checkout_modules.hours.in'                        => trans('checkout.validation.invalid_time_slot'),
            'checkout_modules.staff_member.integer'            => trans('checkout.validation.invalid_staff_member'),
            'checkout_modules.persons_children.adults.min'     => trans('checkout.validation.min_adults'),
            'checkout_modules.persons_children.children.max'   => trans('checkout.validation.max_children'),
            'checkout_modules.persons_children.rooms.min'      => trans('checkout.validation.min_rooms'),
            'checkout_modules.cancellation_policy.accepted'    => trans('checkout.validation.must_agree_policy'),
            'checkout_modules.checkout_message.max'            => trans('checkout.validation.message_too_long'),
            'checkout_modules.reviewer_message.max'            => trans('checkout.validation.message_too_long'),
        ];
    }
}
