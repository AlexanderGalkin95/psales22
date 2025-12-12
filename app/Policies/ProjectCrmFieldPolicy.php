<?php

namespace App\Policies;

use App\Models\ProjectCrmField;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectCrmFieldPolicy
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

    public function beDeleted(User $current, ProjectCrmField $crm)
    {
        if ($crm->ratings()->exists()) {
            return $this->deny("Невозможно удалить поле CRM [{$crm->name}], т.к для этого поля уже есть оценки.");
        }

        return true;
    }
}
