<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Company;

class RestrictCompanyDomains implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $company = Company::leftJoin('projects as pr', 'pr.company_id', '=', 'companies.id')
            ->where(function ($query) use ($value) {
                $query->orWhere('pr.integration_domain', '=', $value)
                    ->orWhere('companies.domain', '=', $value);
            })
            ->where('companies.id', '<>', $this->request->company_id)
            ->first();

        return empty($company);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Данный домен уже используется в другой компании';
    }
}
