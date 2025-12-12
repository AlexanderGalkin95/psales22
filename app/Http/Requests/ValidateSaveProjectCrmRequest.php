<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidateSaveProjectCrmRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->message = 'У вас недостаточно прав для редактирования проекта';
        return Auth::user()->hasRole(["sa", "pm"]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'projectId' => 'required|integer|exists:projects,id',
            'crm' => 'array|nullable',
            'crm.*.name' => 'required|string',
            'crm.*.google_column' => 'required|string',
        ];
    }
}
