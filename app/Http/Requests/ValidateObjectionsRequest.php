<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use App\Traits\FormRequestTrait;

class ValidateObjectionsRequest extends FormRequest
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
            'objections' => 'array|nullable',
            'objections.google_column' => ['alpha', 'max:2', 'regex:/^[A-Z]+$/', $this->requiredIfObjectionsNotEmpty()],
            'objections.google_column_rate' => ['alpha', 'max:2', 'regex:/^[A-Z]+$/', $this->requiredIfObjectionsNotEmpty()],
            'objections.options' => ['array', 'nullable', $this->requiredIfObjectionsNotEmpty()],
            'objections.options.*.name' => 'required|string',
        ];
    }

    protected function requiredIfObjectionsNotEmpty(): RequiredIf
    {
        return Rule::requiredIf(function () {
            return !empty($this->objections);
        });
    }
}
