<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BitrixCallStatus;
use App\Models\HeatType;
use App\Models\AmoCallStatus;
use App\Models\ProjectCallType;
use App\Models\Rating;
use App\Models\RefIntegration;
use App\Models\User;
use App\Repositories\ProjectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class DictionariesController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get roles",
     *     description="Get list of roles",
     *     tags={"Dictionaries"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *      response=200,
     *      description="Success"
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function roles(Request $request): JsonResponse
    {
        $user = User::find(Auth::user()->id);
        $roles = DB::table('roles')
            ->select(
                'id as value',
                'display_name as label'
            )
            ->when(
                !$user->isAdmin() && $user->hasRole('pm'),
                function ($q) {
                    return $q->where('name', '<>', 'sa');
                }
            )
            ->get();

        return response()->json([
            'roles' => $roles,
            'status' => 'success'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{dictionaries}",
     *     summary="Get roles",
     *     description="Get list of roles",
     *     tags={"Dictionaries"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *      name="dictionaries",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Response(
     *      response=200,
     *      description="Success"
     *     )
     * )
     * @param Request $request
     * @param ProjectRepository $pr
     * @param $dictionaries
     * @param null $param
     * @return JsonResponse
     */
    public function index(
        Request $request,
        ProjectRepository $pr,
        $dictionaries,
        $param = null
    ): JsonResponse
    {
        $d = explode('|', $dictionaries);
        $response = [];
        foreach ($d as $dName) {
            switch ($dName) {
                case 'assessors':
                    $response['assessors'] = DB::table('role_user as ru')
                        ->join('users as u', 'u.id', '=', 'ru.user_id')
                        ->join('roles as r', 'r.id', '=', 'ru.role_id')

                        ->select(
                            'u.id',
                            'u.name'
                        )
                        ->where('r.name', 'assessor')
                        ->get();

                    $response['assessors']->transform(function ($i, $k) {
                        $i->value = (int)$i->id;
                        $i->label = $i->name;
                        unset($i->name);
                        return $i;
                    });

                    break;
                case 'senior':
                    $response['senior'] = DB::table('role_user as ru')
                        ->join('users as u', 'u.id', '=', 'ru.user_id')
                        ->join('roles as r', 'r.id', '=', 'ru.role_id')

                        ->select(
                            'u.id',
                            'u.name'
                        )
                        ->where('r.name', 'senior_assessor')
                        ->get();

                    $response['senior']->transform(function ($i, $k) {
                        $i->value = (int)$i->id;
                        $i->label = $i->name;
                        unset($i->name);
                        return $i;
                    });

                    break;
                case 'pm':
                    $response['pm'] = DB::table('role_user as ru')
                        ->join('users as u', 'u.id', '=', 'ru.user_id')
                        ->join('roles as r', 'r.id', '=', 'ru.role_id')

                        ->select(
                            'u.id',
                            'u.name'
                        )
                        ->where('r.name', 'pm')
                        ->get();

                    $response['pm']->transform(function ($i, $k) {
                        $i->value = (int)$i->id;
                        $i->label = $i->name;
                        unset($i->name);
                        return $i;
                    });

                    break;
                case 'ratings':
                    $response['ratings'] = Rating::select([
                            'id as value',
                            'name as label',
                            'system_name',
                            ])->get();
                    break;
                case 'extension':
                    $response['extension'] = DB::table('extension_states')
                        ->select([
                            'id',
                            'name as label',
                            'system_name as value',
                        ])->get();
                    break;
                case 'heat_types':
                    $response['heat_types'] = HeatType::select([
                            'id as value',
                            'name as label',
                            'system_name',
                        ])->get();
                    break;
                case 'call_statuses':
                    $projectId = (int)$request->query('projectId');
                    if (empty($projectId)) {
                        $response['call_statuses'] = [];
                        break;
                    }
                    $project = $pr->findById($projectId, null, ['reference']);
                    if (!$project->reference) {
                        $response['call_statuses'] = [];
                        break;
                    }
                    if ($project->reference->system_name === 'bitrix_24') {
                        $response['call_statuses'] = BitrixCallStatus::select([
                            'id as value',
                            'name as label',
                            'system_name',
                        ])->get();
                    }
                    if ($project->reference->system_name === 'amo_crm') {
                        $response['call_statuses'] = AmoCallStatus::select([
                            'id as value',
                            'name as label',
                            'system_name',
                        ])->get();
                    }

                    break;
                case 'integrations':
                    $response['integrations'] = RefIntegration::select([
                        'id as value',
                        'name as label',
                        'system_name',
                        'validator',
                    ])->get();
                    break;
                case 'roles':
                    $response['roles'] = DB::table('roles')
                        ->select(
                            'id as value',
                            'display_name as label',
                            'name',
                        )
                        ->get();
                    break;
            }
        }
        return response()->json($response);
    }

    public function projectCallTypes($project_id): JsonResponse
    {
        $validator = Validator::make(compact('project_id'), ['project_id' => 'required|integer|exists:projects,id']);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }
        return response()->json(
            ProjectCallType::select([
                'id as value',
                'name as label',
                ])
                ->where('project_id', '=', $project_id)
                ->get()
        );
    }
}
