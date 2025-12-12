<?php

namespace App\Policies;

use App\Models\ProjectCallType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
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

    public function deleteCallTypes(User $current, ProjectCallType $type)
    {
        if ($type->ratings()->exists()) {
            return $this->deny(
                "Невозможно удалить тип звонка [{$type->name}] для текущего проекта,
                т.к у него уже есть оценки."
            );
        }

        return true;
    }
}
