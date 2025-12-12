<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use App\Traits\FormRequestTrait;

class ValidateAdditionalCriteriaRequest extends FormRequest
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
            'additional_criteria' => 'array|nullable',
            'additional_criteria.*.name' => ['string', $this->requiredIfAdditionalCriteriaNotEmpty()],
            'additional_criteria.*.legend' => ['string', $this->requiredIfAdditionalCriteriaNotEmpty()],
            'additional_criteria.*.options' => ['array', 'nullable', $this->requiredIfAdditionalCriteriaNotEmpty()],
            'additional_criteria.*.options.*.label' => 'required|string',
            'additional_criteria.*.options.*.value' => 'required',
        ];
    }

    protected function requiredIfAdditionalCriteriaNotEmpty(): RequiredIf
    {
        return Rule::requiredIf(function () {
            return !empty($this->additional_criteria);
        });
    }
}
