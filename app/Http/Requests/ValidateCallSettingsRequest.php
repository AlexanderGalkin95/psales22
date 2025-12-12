<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidateCallSettingsRequest extends FormRequest
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
            'projectId'        => 'required|integer|exists:projects,id',
            'filter_duration_from'   => 'nullable|integer|min:0|max:86400',
            'filter_duration_to'   => 'nullable|integer|gte:filter_duration_from|max:86400',
            'statuses'         => 'required|array',
            'statuses.*.system_name'   => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'projectId.exists' => 'Проект не найден',
            'statuses.required' => 'Поле Статусы обязательно для заполнения',
            'statuses.*.system_name.required' => 'Поле Статусы обязательно для заполнения',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $messages = $validator->getMessageBag()->getMessages();
        if ($validator->errors()->has(['statuses.*.system_name'])) {
            $messages['statuses'] = $validator->errors()->first('statuses.*.system_name');
        }
        $messages = collect($messages)->filter(function ($item, $key) use ($messages) {
            return !Str::is('statuses.*.system_name', $key);
        })->all();

        throw ValidationException::withMessages($messages);
    }
}
