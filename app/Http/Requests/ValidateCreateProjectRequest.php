<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Rules\RestrictCompanyDomains;

class ValidateCreateProjectRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->message = 'У вас нет недостаточно прав для создания проекта';
        return $this->user()->hasRole(["sa", "pm"]);
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
            'name' => 'required',
            'assessors'         => 'required|array',
            'assessors.*'         => 'required|integer',
            'senior' => 'integer|required',
            'pm' => 'integer|required',
            'integration_domain' => ['required', new RestrictCompanyDomains($this)],
            'googleConnection' => 'required|string',
            'googleSpreadsheet' => 'required|string',
            'google_spreadsheet_id' => 'nullable|string|max:255',
            'rating' => 'required|integer|exists:ref_ratings,id',
            'date_start' => 'required|date',
            'total_time_limit' => 'integer|nullable',
            'permissible_error' => 'integer|between:10,100',
        ];
    }
}
