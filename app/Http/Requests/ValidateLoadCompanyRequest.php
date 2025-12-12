<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class ValidateLoadCompanyRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'companyId' => 'required|integer|exists:companies,id'
        ];
    }

    public function messages(): array
    {
        return [
            'companyId.exists' => 'Компания не найдена'
        ];
    }
}
