<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCreateUserRequest;
use App\Http\Requests\ValidateDeleteUserRequest;
use App\Http\Requests\ValidateUpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\SmsCodeHistory;

class UsersController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit'     => 'numeric|min:0',
            'offset'    => 'numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }
        $limit  = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);

        $mapping = [
            'name'   => 'u.name',
            'email'  => 'u.email',
            'role'  => 'ru.role_id',
        ];
        $query = DB::table('users AS u')
            ->leftJoin('role_user as ru', 'ru.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'r.id', '=', 'ru.role_id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                DB::raw('u.phone::varchar'),
                'u.telegram',
                'u.duo as duoMode',
                'ru.role_id',
                'r.display_name as role',
                'r.name as role_name',
                'u.is_blocked',
            )
            ->when(
                $request->has('$filter'),
                $this->applyFilterClosure($request, $mapping)
            )
            ->when(
                $request->has('search'),
                function ($q) use ($request) {
                    return $q->where(function ($q) use ($request) {
                        $q->Where('u.name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('u.email', 'ilike', '%' . $request->search . '%')
                            ->orWhere('r.display_name', 'ilike', '%' . $request->search . '%');
                    });
                }
            );

        $total = $query->distinct()->get()->count('id');
        $mapping['role']  = 'r.display_name';
        $users = $query->when(
            $request->has('$orderBy'),
            $this->applyOrderByClosure($request, $mapping)
        )
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'total' => $total,
            'users' => $users,
        ]);
    }

    /**
     * @param ValidateDeleteUserRequest $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delete(ValidateDeleteUserRequest $request, $id): JsonResponse
    {
        $user = User::find($id);

        $this->authorize('delete-user', $user);

        $user->delete();

        return response()->json([
            'message' => 'Пользователь был успешно удален',
            'users' => 'ok',
            'status' => 'success'
        ]);
    }

    /**
     * @param ValidateUpdateUserRequest $request
     * @param $userId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(ValidateUpdateUserRequest $request, $userId): JsonResponse
    {
        $user = User::find($userId);

        $this->authorize('edit-user', [$user, $request->input('role')]);

        if ($user->is_blocked && !$request->input('is_blocked')) {
            SmsCodeHistory::where('user_id', $user->id)
                ->where('is_current', true)
                ->update([
                    'attempts' => 0,
                    'sms_send_attempts' => 0
                ]);
        }

        $updatable = [
            'name' => 'name',
            'email' => 'email',
            'phone' => 'phone',
            'is_blocked' => 'is_blocked',
            'telegram' => 'telegram',
            'duoMode' => 'duo',
        ];
        $update = [
            'updated_at' => DB::raw('current_timestamp'),
        ];
        foreach ($updatable as $inputName => $fieldName) {
            if ($request->has($inputName)) {
                $update[$fieldName] = $request->input($inputName);
            }
        }

        if ($request->input('password')) {
            $update['password'] = Hash::make($request->input('password'));
        }

        if ($request->input('is_blocked')) {
            $this->authorize('be-blocked', $user);
        }

        DB::transaction(function () use ($user, $update, $request) {
            $user->update($update);

            $user->detachRole($user->role);
            $user->attachRole($request->role);

            $this->updateTelegram($request, $user->id);
        });

        return response()->json([
            'message' => 'Пользователь был успешно обновлен',
            'users' => 'ok',
            'status' => 'success'
        ]);
    }

    /**
     * @param ValidateCreateUserRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function create(ValidateCreateUserRequest $request): JsonResponse
    {
        $this->authorize('create-user', [$request->user(), $request->input('role')]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'telegram' => $request->telegram,
                'duo' => $request->duoMode,
                'password' => Hash::make($request->input('password')),
            ]);

            $user->attachRole($request->role);

            $this->updateTelegram($request, $user->id);
        });

        return response()->json([
            'message' => 'Пользователь был успешно создан',
            'users' => 'ok',
            'status' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = User::with('roles')
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'telegram',
            )
            ->find([Auth::id()]);
        $user = $user->transform(function ($item) {
            $item->role = !$item->roles->isEmpty() ? $item->roles[0]->display_name : '';
            $item->role_name = !$item->roles->isEmpty() ? $item->roles[0]->name : '';

            unset($item->roles);
            return $item;
        });

        return response()->json([
            'user' => $user[0],
            'status' => 'success'
        ]);
    }
}
