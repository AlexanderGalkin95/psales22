<?php

namespace App\Http\Requests;

use App\Repositories\Facades\ProjectRepository;
use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RestrictCompanyDomains;

class ValidateUpdateProjectRequest extends FormRequest
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
        return $this->user()->hasRole(['sa', 'pm']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'company_id' => 'required|integer|exists:companies,id',
            'projectId' => 'required|integer|exists:projects,id',
            'name' => 'required',
            'pm' => 'required|integer',
            'senior' => 'required|integer',
            'assessors' => 'required|array',
            'assessors.*' => 'required|integer',
            'integration_domain' => ['required', new RestrictCompanyDomains($this)],
            'googleConnection' => 'required',
            'google_spreadsheet_id' => 'nullable|string|max:255',
            'rating' => 'required|integer|exists:ref_ratings,id',
            'date_start' => 'required|date',
            'total_time_limit' => 'integer|nullable',
            'permissible_error' => 'required|integer|between:10,100',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!count($validator->failed())) {
                $projectId = $this->route('projectId');
                $project = ProjectRepository::findById($projectId, [], ['callSettingsSalesManagers']);

                $totalDurationLimit = $project->callSettingsSalesManagers->sum('duration_limit');

                if ($totalDurationLimit > $this->input('total_time_limit')) {
                    $validator->errors()
                        ->add('total_time_limit', 'Общий объём времени на проект меньше, чем общий объём времени по менеджерам');
                }
            }
        });
    }
}
