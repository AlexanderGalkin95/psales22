<?php

namespace App\Http\Requests;

use App\Models\AmoCode;
use App\Models\ProjectObjectionField;
use \App\Repositories\Facades\ProjectRepository;
use App\Traits\FormRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidateAmoStoreRequest extends FormRequest
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
            'projectObjections' => 'required|array',
            'projectObjections.google_column' => 'required|alpha|max:2|regex:/^[A-Z]+$/',
            'projectObjections.google_column_rate' => 'required|alpha|max:2|regex:/^[A-Z]+$/',
            'projectCriteria' => 'required|array|exists:criteria,id',
            'projectCriteria.*' => 'required|exists:criteria,id',
            'projectCrm' => 'required|array',
            'projectCrm.*' => 'required|exists:project_crm_fields,id',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function prepareForValidation()
    {
        $origin = parse_url($this->header('origin'), PHP_URL_HOST);

        $widget_id = AmoCode::where('domain', '=', $origin)->value('id');
        if (empty($widget_id)) {
            throw ValidationException::withMessages(['domain' => "Не найдено в системе интеграции с домена '$origin'"]);
        }
        $project = ProjectRepository::getProjectByAttribute('amo_code_id', $widget_id);
        if ($project instanceof \Illuminate\Http\JsonResponse) {
            throw ValidationException::withMessages(['project' => "Проект не найден"]);
        }
        $projectObjections = ProjectObjectionField::getGoogleColumnKeys($project->id);

        $crm = [];
        $criteria = [];
        foreach ($this->all() as $key => $value) {
            if (preg_match('/criteria_(\d+)$/', $key, $matches)) {
                $criteria[] = $matches[1];
            } elseif (preg_match('/crm_(\d+)$/', $key, $matches)) {
                $crm[] = $matches[1];
            }
        }

        $this->merge([
            'projectCrm' => $crm,
            'projectCriteria' => $criteria,
            'projectObjections' => $projectObjections,
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function failedValidation(Validator $validator)
    {
        $fields = implode(',', $validator->getMessageBag()->keys());

        $messages = [];
        if (Str::contains($fields, 'projectCriteria')) {
            $messages['projectCriteria'] = 'КРИТЕРИИ для проекта неправильно заданы или отсутствуют.';
        }
        if (Str::contains($fields, 'projectCrm')) {
            $messages['projectCrm'] = 'ПОЛЯ CRM для проекта неправильно заданы или отсутствуют.';
        }
        if (Str::contains($fields,'projectObjections')) {
            $messages['projectObjections'] = 'ВОЗРАЖЕНИЯ неправильно заданы или отсутствуют.';
        }

        throw ValidationException::withMessages($messages);
    }
}
