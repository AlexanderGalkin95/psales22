<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class ValidateRecordRequest extends FormRequest
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
            'projectId' => 'required|integer|exists:projects,id',
            'recordId' => 'required|integer|exists:project_calls,call_id',
        ];
    }

    public function messages(): array
    {
        return [
            'projectId.exists' => 'Проект не найден',
            'recordId.exists' => 'Запись не найдена',
        ];
    }
}
