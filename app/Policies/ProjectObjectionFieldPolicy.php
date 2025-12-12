<?php

namespace App\Policies;

use App\Models\ProjectObjectionField;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectObjectionFieldPolicy
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

    public function beDeleted(User $current, ProjectObjectionField $objection)
    {
        if ($objection->ratings()->exists()) {
            return $this->deny("Невозможно удалить тип возражения [{$objection->name}], т.к для этого типа возражения уже есть оценки.");
        }

        return true;
    }
}
