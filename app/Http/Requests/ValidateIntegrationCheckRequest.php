<?php

namespace App\Http\Requests;

use App\Models\RefIntegration;
use App\Repositories\Facades\ProjectRepository;
use App\Traits\FormRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateIntegrationCheckRequest extends FormRequest
{
    use FormRequestTrait;

    protected string $domain_regex = '';

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
            'project_type' => 'required|integer|exists:ref_integrations,id',
            'integration_domain' => ['required', 'string', $this->existsIntegration()]
        ];
    }

    public function messages(): array
    {
        return [
            'integration_domain.exists' => 'Домен не найден. Проверьте правильность написания домена или произведите установку интеграции.'
        ];
    }

    protected function existsIntegration()
    {
        $reference = RefIntegration::where('id', '=', $this->input('project_type'))
            ->first(['system_name', 'validator']);

        if ($reference->system_name === 'amo_crm') {
            $this->domain_regex = $reference->validator;
            return Rule::exists('amo_codes', 'domain');
        }
        if ($reference->system_name === 'bitrix_24') {
            $this->domain_regex = $reference->validator;
            return Rule::exists('bitrix_codes', 'domain');
        }

        return false;
    }

    protected function failedValidation(Validator $validator)
    {
        /* INFO:: Так у "integration_domain" может отсутствовать интеграции,
         | мы обновляем статус в проекте с таким доменом на "FALSE"
        */
        $project = ProjectRepository::findByAttributes(['integration_domain' => $this->input('integration_domain')]);
        if ($project) {
            $project->update([ 'integration_status' => false ]);
        }
        parent::failedValidation($validator);
    }
}
