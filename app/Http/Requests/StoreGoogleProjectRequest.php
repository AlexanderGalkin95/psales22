<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoogleProjectRequest extends FormRequest
{
    use FormRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'google_spreadsheet_id' => 'nullable|string|max:255',
            'report_time' => 'required|string',
            'managers' => 'array|nullable',
            'managers.*' => 'string',
            'telegram' => 'required|string',
            'include_holidays' => 'required|boolean',
            'period' => 'array|nullable',
            'timezone' => 'string|nullable',
            'sending_period' => 'array|nullable',
            'sending_include_holidays' => 'required|boolean',
            'is_active' => 'required|boolean',
            'override_report_sent_at' => 'date|nullable',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'managers' => array_map(fn($item) => $item['name'], $this->get('managers'))
        ]);
    }
}
