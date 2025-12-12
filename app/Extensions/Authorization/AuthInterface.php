<?php

namespace App\Extensions\Authorization;

interface AuthInterface
{
    public function authPermissions(): array;

    public function authCan(string $perm): bool;
}
