<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ValidateDeleteCompanyRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->message = 'У вас недостаточно прав для удаления компании';
        return Auth::user()->hasRole(['sa', 'pm']);
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

    protected function failedValidation(Validator $validator)
    {
        throw ValidationException::withMessages([
            'Компания не найдена'
        ]);
    }
}
