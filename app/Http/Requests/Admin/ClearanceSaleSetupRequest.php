<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ClearanceSaleSetupRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'setup_by' => 'required',
            'discount_type' => 'required',
            'discount_amount' => 'numeric|min:0',
            'offer_active_time' => 'required',
            'clearance_sale_duration' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'setup_by.required' => translate('the_setup_by_field_is_required'),
            'discount_type.required' => translate('the_discount_type_field_is_required'),
            'discount_amount.numeric' => translate('The_discount_amount_must_be_a_number'),
            'discount_amount.min' => translate('the_discount_amount_can_not_less_than_zero'),
            'offer_active_time.required' => translate('the_offer_active_time_field_is_required'),
            'clearance_sale_duration.required' => translate('clearance_sale_duration_is_required'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (empty($this['clearance_sale_duration'])) {
                    $validator->errors()->add(
                        'clearance_sale_duration', translate('clearance_sale_duration_is_required') . '!'
                    );
                } else {
                    $dates = explode(' - ', $this['clearance_sale_duration']);
                    if (count($dates) !== 2 || !checkDateFormatInMDYAndTime($dates[0]) || !checkDateFormatInMDYAndTime($dates[1])) {
                        $validator->errors()->add(
                            'clearance_sale_duration', translate('Invalid_date_range_format') . '!'
                        );
                    }
                }

                if ($this['discount_type'] == 'flat' && $this['discount_amount'] <= 0) {
                    $validator->errors()->add(
                        'discount_amount', translate('discount_amount_is_required') . '!'
                    );
                }

                if ($this['discount_type'] == 'flat' && $this['discount_amount'] > 100) {
                    $validator->errors()->add(
                        'discount_amount', translate('discount_amount_cannot_be_greater_than_100') . '!'
                    );
                }

                if ($this['offer_active_time'] == 'specific_time' && empty($this['offer_active_range'])) {
                    $validator->errors()->add(
                        'offer_active_range', translate('offer_active_range_is_required') . '!'
                    );
                }

                if ($this['offer_active_time'] == 'specific_time' && !empty($this['offer_active_range'])) {
                    $dates = explode(' - ', $this['offer_active_range']);
                    if (count($dates) !== 2 || !checkTimeFormatInRequestTime($dates[0]) || !checkTimeFormatInRequestTime($dates[1])) {
                        $validator->errors()->add(
                            'offer_active_range', translate('Invalid_time_range_format') . '!'
                        );
                    }
                }
            }
        ];
    }

}
