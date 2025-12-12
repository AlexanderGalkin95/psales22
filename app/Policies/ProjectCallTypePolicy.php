<?php

namespace App\Policies;

use App\Models\ProjectCallType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectCallTypePolicy
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

    public function deleteCallType(User $current, ProjectCallType $callType)
    {
        if ($callType->ratings()->exists()) {
            return $this->deny(
                "Невозможно удалить тип звонка [{$callType->name}] для текущего проекта,
                т.к у него уже есть оценки."
            );
        }

        return true;
    }
}
