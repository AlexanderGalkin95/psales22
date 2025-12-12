<?php

namespace App\Extensions\Authorization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PsGate
{

    public PsContext $context;


    private function __construct()
    {
        $this->context = new PsContext();
    }

    public static function make(?array $usersGroupIds = null, ?Model $model = null): self
    {
        $obj = new self();
        $obj->context->user = auth()->user();
        $obj->context->usersIds = $usersGroupIds;
        $obj->context->model = $model;
        return $obj;
    }


    public function forUser(User $user): self
    {
        $this->context->user = $user;
        return $this;
    }


    public static function __callStatic($name, $arguments)
    {
        if ($name === 'check') {
            $permission = $arguments[0];
            $context = $arguments[1] ?? null;
            $obj = new self();
            $obj->context = $context;
            return $obj->check($permission, $context);
        }
    }


    // основная точка входа. Остальные - сахар.
    public function check($permission): bool
    {
        if (!$this->context->user) {
            $this->context->user = auth()->user();
            if (!$this->context->user) {
                return false;
            }
        }
        return Ps::check($this->getUserPermissions(), $permission, $this->context);
    }


    public function getUserPermissions(): array
    {
        $map = [
            //админ
            'sa' => [
                Ps::COMPANY,
                Ps::PROJECT,
                Ps::COMM_RATING,
                Ps::USER,
                Ps::PROJECT_EO,
            ],
            //пм
            'pm' => [
                Ps::COMPANY_VIEW,
                Ps::COMPANY_CREATE,
                Ps::COMPANY_UPDATE,
                Ps::COMPANY_UPDATE,
                Ps::PROJECT,
                Ps::COMM_RATING,
                Ps::USER_VIEW,
                Ps::USER_CREATE,
                Ps::PROJECT_EO_VIEW,
                Ps::PROJECT_EO_CREATE,
                Ps::PROJECT_EO_UPDATE,
            ],
            //старший ассессор
            'senior_assessor' => [
                Ps::COMPANY_VIEW,
                Ps::PROJECT_VIEW,
                Ps::COMM_RATING_VIEW,
                Ps::COMM_RATING_CREATE,
                Ps::COMM_RATING_UPDATE_SELF_GROUP,
                Ps::PROJECT_EO,
            ],
            //ассессор
            'assessor' => [
                Ps::COMPANY_VIEW_SELF,
                Ps::PROJECT_VIEW_SELF,
                Ps::COMM_RATING_VIEW,
                Ps::COMM_RATING_CREATE,
                Ps::COMM_RATING_UPDATE_SELF,
            ],
            //аналитик
            'analytic' => [
                Ps::COMPANY_VIEW,
                Ps::PROJECT_VIEW,
                Ps::COMM_RATING_VIEW,
                Ps::USER_VIEW,
                Ps::PROJECT_EO,
            ],
            //тех.поддержка
            'technical_support' => [
                Ps::COMPANY,
                Ps::PROJECT,
                Ps::COMM_RATING,
                Ps::USER_VIEW,
                Ps::USER_CREATE,
                Ps::USER_UPDATE,
                Ps::PROJECT_EO,
            ],
        ];
        $user = $this->context->user;
        $rolesNames = (array)$user->getRoles();
        $perms = [];
        foreach ($rolesNames as $roleName) {
            $rolePerms = $map[$roleName] ?? [];
            $perms = array_merge($perms, $rolePerms);
        }

        return array_unique($perms);
    }


}
