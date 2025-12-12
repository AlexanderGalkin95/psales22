<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class ValidateUpdateCompanyRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->message = 'У вас нет недостаточно прав для обновления данных компании';
        return Auth::user()->hasRole(["sa", "pm"]);
    }

    public function prepareForValidation()
    {
        $parameters = $this->all();
        $parameters['managers'] = array_map(function ($item) {
            return ['user_id' => $item];
        }, $parameters['managers'] ?? []);

        $this->merge($parameters);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $companyId = $this->route('companyId');
        return [
            'companyId' => 'required|integer|exists:companies,id',
            'name' => 'required',
            'description' => 'required|string',
            'niche' => 'required|string',
            'domain' => 'nullable|unique:companies,domain,' . $companyId,
            'active' => 'required|boolean',
            'admin_id' => 'integer|exists:users,id',
            'logo' => 'string|nullable',
            'contact_name' => 'required|string',
            'contact_phone' => 'string|regex:/^[0-9]*$/|min:10|max:15|nullable|unique:companies,contact_phone,'. $companyId . ',id',
            'contact_tariff' => 'required|integer',
            'contact_agreement' => 'required|string',
            'managers' => 'array',
            'managers.*.user_id' => ['integer', 'exists:users,id', $this->requiredIfManagersNotEmpty()],
            'links' => 'array',
            'links.*.title' => ['string', $this->requiredIfLinksNotEmpty()],
            'links.*.link' => 'string|nullable',
            'links.*.static' => 'boolean|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'companyId.exists' => 'Компания не найдена'
        ];
    }

    protected function requiredIfLinksNotEmpty(): RequiredIf
    {
        return Rule::requiredIf(function () {
            return !empty($this->links);
        });
    }

    protected function requiredIfManagersNotEmpty(): RequiredIf
    {
        return Rule::requiredIf(function () {
            return !empty($this->managers);
        });
    }
}
