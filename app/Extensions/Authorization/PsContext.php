<?php

namespace App\Extensions\Authorization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PsContext
{
    public ?User $user = null;
    public ?array $usersIds = null;
    public ?Model $model = null;
}
