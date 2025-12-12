<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidateSaveProjectCallTypesRequest extends FormRequest
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
            'call_types' => 'array|nullable',
            'call_types.*.id' => 'nullable|exists:project_call_types,id',
            'call_types.*.name' => 'required|string',
            'call_types.*.short_name' => 'required|string',
            'call_types.*.rate_crm' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'projectId.exists' => 'Проект не найден',
            'call_types.*.id.exists' => 'Один или несколько типов звонков не найдены',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $messages = $validator->getMessageBag()->getMessages();
        if ($validator->errors()->has('call_types.*.id')) {
            $messages['call_types.exists'] = $validator->errors()->first('call_types.*.id');
        }
        $messages = collect($messages)->filter(function ($item, $key) use ($messages) {
            return !Str::is('call_types.*.id', $key);
        })->all();

        throw ValidationException::withMessages($messages);
    }
}
