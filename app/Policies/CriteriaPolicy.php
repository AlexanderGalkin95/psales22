<?php

namespace App\Policies;

use App\Models\Criteria;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CriteriaPolicy
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

    public function beDeleted(User $current, Criteria $criteria)
    {
        if($criteria->ratings()->exists()) {
            return $this->deny("Невозможно удалить критерий [{$criteria->name}], т.к для этого критерия уже есть оценки.");
        }

        return true;
    }
}
