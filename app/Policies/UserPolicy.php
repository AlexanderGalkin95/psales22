<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;

class UserPolicy
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

    public function createUser(User $current, User $user, $role)
    {
        if ($current->isAdmin()) {
            return true;
        }

        if ($current->isA('pm')) {
            $role = Role::where('id', '=', $role)->first();

            if ($role->name === 'sa') {
                return $this->deny('У вас недостаточно прав для назначения пользователям роли администратора');
            }

            return true;
        }

        return $this->deny('У вас недостаточно прав для создания пользователя с правами администратора');
    }

    public function editUser(User $current, User $user, $role)
    {
        if ($current->isAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->deny('У вас недостаточно прав для обновления данных администратора');
        }

        if ($current->isA('pm')) {
            $role = Role::where('id', '=', $role)->first();

            if ($role->name === 'sa') {
                return $this->deny('У вас недостаточно прав для назначения пользователям роли администратора');
            }

            return true;
        }

        return $this->deny('У вас недостаточно прав для обновления данных пользователя');
    }

    public function deleteUser(User $current, User $user)
    {
        if (!$user->projects()->isEmpty()) {
            return $this->deny('Невозможно удалить данного пользователя т.к у этого пользователя уже есть проекты.');
        }

        if ($user->id == $current->id) {
            return $this->deny('Невозможно удалить себя');
        }

        if ($current->isAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->deny('Невозможно удалить данного пользователя');
        }

        if ($current->isA('pm')) {
            return true;
        }

        return $this->deny('Невозможно удалить данного пользователя');
    }

    public function beBlocked(User $current, User $user)
    {
        if (!$user->isBlocked() && $user->id == $current->id) {
            return $this->deny('Невозможно заблокировать себя');
        }

        return true;
    }
}
