<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Company;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function deleteCompany(User $current, Company $company)
    {
        if($company->projects_count > 0) {
            return $this->deny("Невозможно удалить компанию [{$company->name}], т.к у этой компании есть проекты.");
        }

        return true;
    }
}
