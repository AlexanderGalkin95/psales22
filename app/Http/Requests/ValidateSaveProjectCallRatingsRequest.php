<?php

namespace App\Http\Requests;

use App\Repositories\Facades\ProjectRepository;
use App\Traits\FormRequestTrait;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidateSaveProjectCallRatingsRequest extends FormRequest
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
            'audio' => 'required|string',
            'audio_id' => 'required|integer',
            'date' => 'required|string',
            'time' => 'required|string',
            'comments' => 'required|string',
            'call_type_id' => 'required|integer|exists:project_call_types,id',
            'heat' => 'required|integer',
            'link_to_lead' => 'required|string',
            'manager' => 'required|string',
            'call_type' => 'required|in:inbound,outbound',
            'objection' => 'integer|nullable',
            'objection_rate' => 'integer|nullable',
            'criteria' => 'required|array',
            'criteria.*.id' => 'required|exists:criteria,id',
            'criteria.*.value' => 'required_if:criteria.*.disabled,false',
            'crm' => 'required|array',
            'crm.*.id' => 'required|exists:project_crm_fields,id',
            'additional_criteria' => 'array',
            'additional_criteria.*.id' => 'required|exists:additional_criteria,id',
            'additional_criteria.*.option_id' => 'nullable|exists:additional_criteria_options,id',
            'projectCrm' => 'required|array',
            'projectCrm.*' => 'required',
            'projectCriteria' => 'required|array',
            'projectCriteria.*' => 'required',
            'projectObjections' => 'required|array',
            'projectObjections.*.google_column' => 'required|alpha|max:2|regex:/^[A-Z]+$/',
            'projectObjections.*.google_column_rate' => 'required|alpha|max:2|regex:/^[A-Z]+$/',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function prepareForValidation()
    {
        $project = ProjectRepository::findById($this->route('projectId'), null, ['criteria', 'crm', 'objections']);

        if ($project instanceof \Illuminate\Http\JsonResponse) {
            throw ValidationException::withMessages(['project' => "Проект не найден"]);
        }

        $this->merge([
            'projectCrm' => $project->crm->toArray(),
            'projectCriteria' => $project->criteria->toArray(),
            'projectObjections' => $project->objections->toArray()
        ]);
    }

    /**
     * @throws Exception
     */
    protected function failedValidation(Validator $validator)
    {
        $fields = implode(',', $validator->getMessageBag()->keys());
        $messages = $validator->getMessageBag()->getMessages();
        $keys = Arr::where($messages, function ($message, $field) {
            return Str::contains($field, ['criteria', 'projectCriteria'])
                || Str::contains($field, ['crm', 'projectCrm'])
                || Str::contains($field, ['objection', 'projectObjections']);
        });
        Arr::forget($messages, array_keys($keys));

        if (Str::contains($fields, ['criteria', 'projectCriteria'])) {
            $messages['criteria'] = 'КРИТЕРИИ для проекта неправильно заданы или отсутствуют';
        }
        if (Str::contains($fields, ['crm', 'projectCrm'])) {
            $messages['crm'] = 'ПОЛЯ CRM для проекта неправильно заданы или отсутствуют';
        }
        if (Str::contains($fields, ['objection', 'projectObjections'])) {
            $messages['objection'] = 'ВОЗРАЖЕНИЯ неправильно заданы или отсутствуют';
        }

        throw ValidationException::withMessages($messages);
    }

    public function messages(): array
    {
        return [
            'projectId.exists' => 'Проект не найден'
        ];
    }
}
