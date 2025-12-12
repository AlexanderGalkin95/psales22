<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidateSaveCriteriaRequest extends FormRequest
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
            'projectId'                 => 'required|integer|exists:projects,id',
            'criteria'                  => 'required|array',
            'criteria.*.label'          => 'required|string',
            'criteria.*.text'           => 'required|string',
            'criteria.*.google_column'  => 'required|alpha|max:2|regex:/^[A-Z]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'max' => 'Количество символов не может превышать 2.'
        ];
    }
}
