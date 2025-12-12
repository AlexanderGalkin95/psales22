<?php

namespace App\Http\Controllers\API;

use App\Events\ProjectCreatedEvent;
use App\Events\ProjectEditedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateAdditionalCriteriaRequest;
use App\Http\Requests\ValidateCallSettingsRequest;
use App\Http\Requests\ValidateCreateProjectRequest;
use App\Http\Requests\ValidateLoadProjectCallsRequest;
use App\Http\Requests\ValidateIntegrationCheckRequest;
use App\Http\Requests\ValidateLoadProjectRequest;
use App\Http\Requests\ValidateObjectionsRequest;
use App\Http\Requests\ValidateProjectsListRequest;
use App\Http\Requests\ValidateRecordRequest;
use App\Http\Requests\ValidateSaveCriteriaRequest;
use App\Http\Requests\ValidateSaveProjectCallRatingsRequest;
use App\Http\Requests\ValidateSaveProjectCallTypesRequest;
use App\Http\Requests\ValidateSaveProjectCrmRequest;
use App\Http\Requests\ValidateUpdateProjectRequest;
use App\Jobs\DistributeIntegrationDataByProject;
use App\Jobs\SendCallRatingsToGoogleSheets;
use App\Models\AmoCallStatus;
use App\Models\AmoCode;
use App\Models\BitrixCallStatus;
use App\Models\BitrixCode;
use App\Models\Call;
use App\Models\CallRating;
use App\Models\HeatType;
use App\Models\ProjectCallType;
use App\Models\Criteria;
use App\Models\IntegrationSchedule;
use App\Models\Project;
use App\Models\ProjectCrmField;
use App\Models\ProjectObjectionField;
use App\Models\RefIntegration;
use App\Models\ProjectCallSetting;
use App\Models\ProjectCallSettingsSalesManager;
use App\Models\ProjectCallSettingsIntegrationPipeline;
use App\Models\User;
use App\Services\Bitrix\Facades\BitrixHelper;
use App\Services\ProjectSpreadsheet;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use App\Services\Google\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Repositories\ProjectRepository;
use App\Services\Google\Exceptions\GoogleSpreadsheetException;
use App\Services\Google\Exceptions\GoogleWorksheetException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use stdClass;

/**
 * @OA\Schema(
 *      schema="Project",
 *      @OA\Property(property="id", type="integer", format="int32"),
 *      @OA\Property(property="name", type="string"),
 *      @OA\Property(property="integration_domain", type="string"),
 *      @OA\Property(property="googleConnection", type="string"),
 *      @OA\Property(property="pm_name", type="string"),
 *      @OA\Property(property="senior_name", type="string"),
 *      @OA\Property(property="assessor_name", type="string"),
 *      @OA\Property(property="pm", type="object",
 *          @OA\Property(property="label", description="", type="string"),
 *          @OA\Property(property="value", description="", type="string")
 *      ),
 *      @OA\Property(property="senior", type="object",
 *          @OA\Property(property="label", description="", type="string"),
 *          @OA\Property(property="value", description="", type="string")
 *      ),
 *      @OA\Property(property="assessor", type="object",
 *          @OA\Property(property="label", description="", type="string"),
 *          @OA\Property(property="value", description="", type="string")
 *      ),
 *      @OA\Property(property="criteria", type="array",
 *          @OA\Items(
 *            type="object"
 *        )
 *      )
 *  )
 */
class ProjectsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/project",
     *     summary="Create new project",
     *     description="Create new project",
     *     tags={"Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="assessor",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="senior",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="pm",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="integration_domain",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="google_conection",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="criteria",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *        type="array",
     *        @OA\Items(
     *          @OA\Property(property="label", description="", type="string"),
     *          @OA\Property(property="text", description="", type="string"),
     *        )
     *      )
     *     ),
     *     @OA\Response(
     *      response=200,
     *      description="Success"
     *     )
     * )
     * @param ValidateCreateProjectRequest $request
     * @param ProjectRepository $pr
     * @return JsonResponse
     */
    public function create(ValidateCreateProjectRequest $request, ProjectRepository $pr): JsonResponse
    {
        $project = new stdClass();

        $insertable = [
            'company_id' => $request->company_id,
            'name' => $request->name,
            'pm_id' => $request->pm,
            'senior_id' => $request->senior,
            'rating_id' => $request->rating,
            'project_type' => $request->project_type,
            'integration_domain' => $request->integration_domain,
            'integration_status' => false,
            'google_conection' => $request->googleConnection,
            'google_spreadsheet' => $request->get('googleSpreadsheet'),
            'google_spreadsheet_id' => $request->google_spreadsheet_id,
            'date_start' => $request->date_start,
            'total_time_limit' => $request->total_time_limit,
            'permissible_error' => $request->permissible_error,
        ];

        DB::transaction(function () use ($request, &$project, $insertable, $pr) {

            $project = $pr->create($insertable);

            $project->assessors()->sync($request->assessors);

            $project->attachIntegration($request->input('integration_domain'));
        });

        $project = $pr->getProject($project->id);

        event(new ProjectCreatedEvent($project->id));

        DB::commit();

        return response()->json([
            'project' => $project,
            'message' => 'Проект был успешно создан',
            'status' => 'success'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{id}",
     *     summary="Update project",
     *     description="Update project",
     *     tags={"Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="assessor",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="senior",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="pm",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="integration_domain",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="google_conection",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *       type="string"
     *      )
     *     ),
     *     @OA\Parameter(
     *      name="criteria",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *        type="array",
     *        @OA\Items(
     *          @OA\Property(property="label", description="", type="string"),
     *          @OA\Property(property="text", description="", type="string"),
     *        )
     *      )
     *     ),
     *     @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *           @OA\Property(
     *             property="total",
     *             type="integer"
     *           ),
     *          @OA\Property(
     *              property="projects",
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Project")
     *          )
     *         )
     *       )
     *     )
     * )
     * @param ValidateUpdateProjectRequest $request
     * @param ProjectRepository $pr
     * @param $projectId
     * @return JsonResponse
     */
    public function update(ValidateUpdateProjectRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId);

        $oldUsers = array_filter([$project->pm_id, $project->senior_id, ...$project->assessors->pluck('user_id')->toArray()]);

        $updatable = [
            'company_id' => $request->company_id,
            'name' => $request->name,
            'pm_id' => $request->pm,
            'senior_id' => $request->senior,
            'project_type' => $request->project_type,
            'integration_domain' => $request->integration_domain,
            'rating_id' => $request->rating,
            'google_conection' => $request->googleConnection,
            'google_spreadsheet' => $request->get('googleSpreadsheet'),
            'google_spreadsheet_id' => $request->google_spreadsheet_id,
            'date_start' => $request->date_start,
            'total_time_limit' => $request->total_time_limit,
            'permissible_error' => $request->permissible_error,
        ];

        DB::transaction(function () use ($request, $project, $updatable) {

            $project->update($updatable);

            $project->assessors()->sync($request->assessors);

            $project->attachIntegration($request->input('integration_domain'));
        });

        $project = $pr->getProject($projectId);

        //event(new ProjectEditedEvent($projectId, array_values($oldUsers)));

        return response()->json([
            'project' => $project,
            'message' => 'Проект был успешно обновлен',
            'status' => 'success'
        ]);
    }

    public function saveCriteria(ValidateSaveCriteriaRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['criteria']);
        if (empty($request->criteria)) {
            $project->criteria->each(function ($criteria) {
                $criteria->delete();
            });
            $project->load('criteria');

            return response()->json([
                'status' => 'success',
                'criteria' => $project->criteria,
                'message' => 'Критерии проекта были успешно обновлены',
            ]);
        }
        $deletable = $project->criteria->pluck('id')->toArray();
        $data = collect($request->criteria)
            ->transform(function ($item) use (&$deletable) {
                $item["name"] = $item["label"];
                $item["legend"] = $item["text"];
                if (!empty($item['id'])) {
                    $deletable = array_diff($deletable, [$item['id']]);
                    $model = Criteria::find($item['id']);
                    $model->fill($item);
                } else {
                    $model = new Criteria($item);
                }

                return $model;
            });

        DB::transaction(function () use ($project, $data, $deletable) {
            $project->criteria()->saveMany($data);

            $project->criteria()->whereIn('id', $deletable)->each(function ($criteria) {

                $this->authorize('beDeleted', $criteria);

                $criteria->delete();
            });
        });

        return response()->json([
            'status' => 'success',
            'criteria' => Criteria::where('project_id', $projectId)
                ->orderBy('index_number')
                ->get([
                    'id',
                    'name as label',
                    'legend as text',
                    'index_number',
                    'google_column',
                ]),
            'message' => 'Критерии проекта были успешно обновлены',
        ]);
    }

    public function saveAdditionalCriteria(ValidateAdditionalCriteriaRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['additionalCriteria']);
        $requestData = $request->additional_criteria;
        $deletable = [];
        $deletableOptions = [];

        if (empty($requestData)) {
            if ($project->additionalCriteria) {
                $project->additionalCriteria->each(function ($model) {
                    $model->delete();
                });
            }

            return response()->json([
                'status' => 'success',
                'criteria' => [],
                'message' => 'Дополнительные критерии проекта были успешно обновлены',
            ]);
        }

        if (!$project->additionalCriteria->isEmpty()) {
            $project->additionalCriteria->each(function ($item) use ($requestData, &$deletable, &$deletableOptions) {
                $requestData = collect($requestData);
                if (!$requestData->contains('id', '=', $item->id)) {
                    // Удаляем весь дополнительный критерий с своими options
                    $deletable[] = $item->id;
                } else {
                    // Проверяем наличие options, которые можно удалить для обновляемого критерия
                    $itemRequestOptions = $requestData->where('id', '=', $item->id)->pluck('options');
                    $itemDeletableIds = $item->options->filter(function ($option) use ($item, $itemRequestOptions) {
                        return !$itemRequestOptions->contains('id', '=', $option->id);
                    })->pluck('id')
                        ->toArray();
                    $deletableOptions = array_merge($deletableOptions, $itemDeletableIds);
                }
            });
        }

        DB::transaction(function () use ($project, $deletable, $deletableOptions, $requestData) {
            $project->additionalCriteria()->whereIn('id', $deletable)->each(function ($item) {
                $item->delete();
            });

            foreach ($requestData as $datum) {
                $criteria = $project->additionalCriteria()
                    ->updateOrCreate(['project_id' => $project->id, 'id' => $datum['id'] ?? null], $datum);

                $criteria->options()->whereIn('id', $deletableOptions)->each(function ($option) {
                    $option->delete();
                });

                foreach ($datum['options'] as $option) {
                    $criteria->options()
                        ->updateOrCreate(['id' => $option['id'] ?? null], $option);
                }
            }
        });

        $project->load('additionalCriteria');

        return response()->json([
            'status' => 'success',
            'additional_criteria' => $project->additionalCriteria,
            'message' => 'Дополнительные критерии проекта были успешно обновлены',
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function saveCallTypes(ValidateSaveProjectCallTypesRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['call_types']);

        if (empty($request->call_types)) {
            DB::transaction(function () use ($project) {
                $project->call_types->each(function ($type) {
                    // INFO:: удаление допустимо только, если у проекта нет оценок
                    $this->authorize('delete-call-type', $type);
                    $type->delete();
                });
            });

            return response()->json([
                'status' => 'success',
                'call_types' => [],
                'message' => 'Типы звонков проекта были успешно обновлены',
            ]);
        }

        // INFO:: Массив типов звонков, которые будут удалены
        $deletable = $project->call_types->filter(function ($type) use ($request) {
            return !collect($request->call_types)->contains('id', '=', $type->id);
        })->pluck('id');


        // INFO:: Формируем массив моделей: (обновление или создание - hasMany)
        $models = collect($request->call_types)
            ->transform(function ($item) use ($project, &$deletable) {
                return ProjectCallType::firstOrNew(
                    [
                        'id' => $item['id'] ?? null,
                        'project_id' => $project->id,
                    ],
                    $item
                )->fill($item);
            });

        DB::transaction(function () use ($project, $models, $deletable) {
            $project->call_types()->saveMany($models);

            $project->call_types->whereIn('id', $deletable)->each(function ($type) {
                // INFO:: удаление допустимо только, если у проекта нет оценок
                $this->authorize('delete-call-type', $type);
                $type->delete();
            });
        });

        $project->load('call_types');

        return response()->json([
            'status' => 'success',
            'call_types' => $project->call_types,
            'message' => 'Типы звонков проекта были успешно обновлены',
        ]);
    }

    public function saveSettings(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if ($user->hasRole(["sa", "pm"]) === false) {
            return response()->json([
                'message' => 'У вас недостаточно прав для редактирования проекта',
                'status' => 'error',
            ], 403);
        }

        $validator = Validator::make(
            $request->all() + compact('id'),
            [
                'id' => 'required|integer|exists:projects,id',
                'settings' => 'required|array',
                'settings.*' => 'required|array',
                'settings.*.*' => 'required|array',
                'settings.*.*.call_type_id' => 'required|integer|exists:project_call_types,id',
                'settings.*.*.criteria_id' => 'required|integer|exists:criteria,id',
                'settings.*.*.enabled' => 'required|boolean',
                'settings.*.*.points' => 'integer|nullable',
            ]
        );
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        DB::transaction(function () use ($id, $request) {
            DB::table('project_settings')
                ->where('project_id', $id)
                ->delete();

            foreach ($request->settings as $criteria_id => $setting) {
                foreach ($setting as $item) {
                    DB::table('project_settings')->insert([
                        'project_id' => $id,
                        'criteria_id' => $criteria_id,
                        'call_type_id' => $item['call_type_id'],
                        'enabled' => $item["enabled"],
                        'points' => $item["enabled"] ? $item["points"] : null,
                        'created_at' => DB::raw('now()')
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'settings' => DB::table('project_settings')
                ->select(
                    'criteria_id',
                    'project_id',
                    'call_type_id',
                    'enabled',
                    'points',
                )
                ->where('project_id', '=', $id)
                ->get()
                ->groupBy('criteria_id'),
            'message' => 'Настройки звонков и критериев проекта были успешно обновлены',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{id}",
     *     summary="Get project details",
     *     description="Get project details",
     *     tags={"Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *       type="integer"
     *      )
     *     ),
     *     @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(ref="#/components/schemas/Project")
     *      )
     *     )
     * )
     * @param ValidateLoadProjectRequest $request
     * @param ProjectRepository $pr
     * @param $projectId
     * @return JsonResponse
     */
    public function project(
        ValidateLoadProjectRequest $request,
        ProjectRepository $pr,
        $projectId
    ): JsonResponse {
        $project = $pr->getProject($projectId);

        return response()->json([
            'project' => $project,
            'status' => 'success'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/projects",
     *     summary="Get projects",
     *     description="Get projects",
     *     tags={"Project"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *      response=200,
     *      description="Success",
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *           @OA\Property(
     *             property="total",
     *             type="integer"
     *           ),
     *          @OA\Property(
     *              property="projects",
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Project")
     *          )
     *        )
     *      )
     *    )
     * )
     * @param ValidateProjectsListRequest $request
     * @param ProjectRepository $pr
     * @return JsonResponse
     */
    public function list(ValidateProjectsListRequest $request, ProjectRepository $pr): JsonResponse
    {
        return response()->json($pr->list($request));

        //todo develop only for new frontend!!!
        //todo standard validation error
        $validator = Validator::make($request->all(), [
            'limit' => 'numeric|min:0',
            'offset' => 'numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        /** @var User $user */
        $user = $request->user();

        $limit = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);

        $query = Project::query();

        // pagination
        $query->skip($offset)->take($limit);

        // add columns
        $query->leftJoin('users as users_pm', 'users_pm.id', '=', 'projects.pm_id');
        $query->leftJoin('users as users_senior', 'users_senior.id', '=', 'projects.senior_id');
        $query->leftJoin('ref_integrations', 'ref_integrations.id', '=', 'projects.project_type');

        // sorting
        if ($orderData = $request->input('$orderBy')) {
            [$param, $direction] = explode(' ', $orderData);
            $paramsToColumnsMap = [
                'name' => 'projects.name',
                'pm_name' => 'users_pm.name',
                'senior_name' => 'users_senior.name',
                'reference_name' => 'ref_integrations.name',
                'status' => 'projects.integration_status',
            ];
            $column = $paramsToColumnsMap[$param];
            $query->orderBy($column, $direction);
        }
        // filtering


        // authorization filtering
        if ($user->isAn('assessor')) {
            $query->whereHas('assessors', function ($q) use ($user) {
                $q->where('user_id', '=', $user->id);
            });
        }

        return [];
    }


    public function userProjects(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'numeric|min:0',
            'offset' => 'numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }
        $limit = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);
        $user = Auth::user();

        $query = Project::leftJoin('integrations as int', 'int.id', '=', 'projects.integration_id')
            ->join('ref_integrations as refint', 'refint.id', '=', 'int.ref_integration_id')
            ->leftJoin('amo_codes as ac', 'ac.id', '=', 'int.integration_id')
            ->leftJoin('amo_tokens as at', 'at.amo_code_id', '=', 'ac.id')
            ->select(
                'projects.id',
                'projects.name',
                'projects.integration_id',
                'ac.domain',
                'at.access_token',
            )
            ->where('projects.pm_id', $user->id)
            ->orWhere('projects.senior_id', $user->id)
            ->orWhere('projects.assessor_id', $user->id)
            ->where('refint.type', '=', 'amo_crm');

        $total = $this->getCount($query);
        $projects = $query
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'total' => $total,
            'projects' => $projects
        ]);
    }

    /**
     * @throws \Exception
     */
    public function checkGoogleSheet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'google_spreadsheet_id' => 'required|string',
            'google_connection' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        try {
            $googleService = new GoogleService();
            $googleService
                ->setSpreadsheetName($request->get('google_spreadsheet'))
                ->setSpreadsheetId($request->get('google_spreadsheet_id'))
                ->setTabName($request->get('google_connection'))
                ->validate();
        } catch (\Exception $e) {
            report($e);
            $fields = [];

            if ($e instanceof GoogleSpreadsheetException) {
                $fields['google_spreadsheet_id'] = [$e->getMessage()];
            } elseif ($e instanceof GoogleWorksheetException) {
                $fields['google_connection'] = [$e->getMessage()];
            } else {
                //unknown error
                $fields['google_spreadsheet_id'] = ['Ошибка получения доступа к файлу. См.логи'];
            }

            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => "Связь с Google таблицей {$request->get('google_connection')} потеряна!",
                'fields' => $fields
            ], 422);
        }
        return response()->json([
            'status' => 'success',
            'code' => 2,
            'message' => "Связь с Google таблицей {$request->get('google_connection')} установлена успешно",
        ]);
    }

    public function settings(Request $request, $id): JsonResponse
    {
        $validator = Validator::make(compact('id'), [
            'id' => 'required|integer|exists:projects,id',
        ]);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        $settings = DB::table('criteria as c')
            ->crossJoin('project_call_types as ct')
            ->leftJoin('project_settings as ps', function ($query) {
                $query->on('ps.criteria_id', '=', 'c.id')
                    ->on('ps.call_type_id', '=', 'ct.id');
            })
            ->join('projects as p', function ($query) {
                $query->on('p.id', '=', 'c.project_id')
                    ->on('p.id', '=', 'ct.project_id');
            })
            ->where('c.project_id', '=', $id)
            ->where('ct.project_id', '=', $id)
            ->select(
                'ct.name',
                'p.id as project_id',
                'c.id as criteria_id',
                'ct.id as call_type_id',
                DB::raw('coalesce(ps.enabled, true) as enabled'),
                'ps.points'
            )
            // @TODO: Убрать в следующем релизе
            ->distinct()
            ->get()
            ->sortBy('call_type_id')
            ->groupBy('criteria_id');

        return response()->json([
            'status' => 'success',
            'settings' => $settings
        ]);
    }

    public function saveProjectCrm(ValidateSaveProjectCrmRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['crm']);
        if (empty($request->crm)) {
            $project->crm->each(function ($crm) {
                $this->authorize('beDeleted', $crm);
                $crm->delete();
            });
            $project->load('crm');

            return response()->json([
                'status' => 'success',
                'crm' => $project->crm,
                'message' => 'Поля CRM проекта были успешно обновлены',
            ]);
        }

        $deletable = $project->crm->pluck('id')->toArray();
        $models = collect($request->crm)
            ->transform(function ($item) use (&$deletable) {
                if (!empty($item['id'])) {
                    $deletable = array_diff($deletable, [$item['id']]);
                    $model = ProjectCrmField::find($item['id']);
                    $model->fill($item);
                } else {
                    $model = new ProjectCrmField($item);
                }

                return $model;
            });

        DB::transaction(function () use ($project, $models, $deletable) {
            $project->crm()->saveMany($models);

            $project->crm()->whereIn('id', $deletable)->each(function ($crm) {

                $this->authorize('beDeleted', $crm);

                $crm->delete();
            });
        });

        $project->load([
            'crm' => function (Relation $query) {
                return $query->orderBy('index_number');
            }
        ]);

        return response()->json([
            'status' => 'success',
            'crm' => $project->crm,
            'message' => 'Поля CRM проекта были успешно обновлены',
        ]);
    }

    public function saveProjectObjectionFields(ValidateObjectionsRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['objections']);
        if (empty($request->objections)) {
            $project->objections->each(function ($objection) {
                $objection->delete();
            });
            $project->load('objections');

            return response()->json([
                'status' => 'success',
                'objections' => $project->objections,
                'message' => 'Возражения были обновлены успешно',
            ]);
        }

        $deletable = $project->objections->pluck('id')->toArray();
        $googleColumn = $request->objections['google_column'];
        $googleColumnRate = $request->objections['google_column_rate'];
        $models = collect($request->objections['options'])
            ->transform(function ($item) use ($projectId, $googleColumn, $googleColumnRate, &$deletable) {
                $data = [
                    'name' => $item['name'],
                    'google_column' => $googleColumn,
                    'google_column_rate' => $googleColumnRate,
                    'project_id' => $projectId,
                ];
                if (!empty($item['id'])) {
                    $deletable = array_diff($deletable, [$item['id']]);
                    $model = ProjectObjectionField::find($item['id']);
                    $model->fill($data);
                } else {
                    $model = new ProjectObjectionField($data);
                }

                return $model;
            });

        DB::transaction(function () use ($project, $models, $deletable) {
            $project->objections()->saveMany($models);

            $project->objections()->whereIn('id', $deletable)->each(function ($objection) {

                $this->authorize('beDeleted', $objection);

                $objection->delete();
            });
        });

        $objections = ProjectObjectionField::where('project_id', $projectId)
            ->select([
                'id',
                'name',
                'google_column',
                'google_column_rate',
            ])
            ->get()
            ->groupBy('google_column')
            ->mapWithKeys(function ($item, $key) {
                return [
                    'google_column' => $key,
                    'google_column_rate' => $item[0]->google_column_rate,
                    'options' => $item,
                ];
            });

        return response()->json([
            'status' => 'success',
            'objections' => $objections,
            'message' => 'Возражения были обновлены успешно',
        ]);
    }

    public function loadProjectCallRatings(Request $request, $projectId): JsonResponse
    {
        $validator = Validator::make($request->all() + compact('projectId'), [
            'limit' => 'numeric|min:0',
            'offset' => 'numeric|min:0',
            'projectId' => 'required|integer|exists:projects,id'
        ]);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        $limit = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);

        $callRatings = CallRating::with([
            'criteria',
            'crm',
            'objection',
            'assessor',
            'settings',
            'additionalCriteria',
        ])->whereNotNull('duration')
            ->where('project_id', $projectId)
            ->orderByDesc('created_date')
            ->orderByDesc('created_time');

        $total = $callRatings->count('call_ratings.*');

        $callRatings = $callRatings->skip($offset)->take($limit)->get();

        $project = Project::with([
            'criteria',
            'crm',
            'objections',
            'assessors',
            'settings',
        ])->find($projectId);

        return response()->json([
            'total' => $total,
            'headers' => ProjectSpreadsheet::mapHeaders($project),
            'ratings' => ProjectSpreadsheet::mapData($callRatings, $project)
        ]);
    }

    public function loadProjectCalls(
        ValidateLoadProjectCallsRequest $request,
        ProjectRepository $pr,
        $projectId
    ): JsonResponse {
        /** @var Project $project */
        $project = $pr->findById($projectId);

        if (!$project->hasIntegration()) {
            return response()->json([
                'total' => 0,
                'calls' => [],
            ]);
        }

        /** @var AmoCode|BitrixCode $integrable */
        $integrable = resolve($project->reference->type);

        [$total, $calls] = $integrable->loadCalls($request, $projectId);

        return response()->json([
            'total' => $total,
            'calls' => $calls,
        ]);
    }


    /**
     * @param ValidateSaveProjectCallRatingsRequest $request
     * @param ProjectRepository $pr
     * @param $projectId
     * @return JsonResponse
     */
    public function saveProjectCallRatings(
        ValidateSaveProjectCallRatingsRequest $request,
        ProjectRepository $pr,
        $projectId
    ): JsonResponse {
        $data = $request->all();
        /** @var Project $project */
        $project = $pr->findById($projectId, null, ['objections', 'call_types']);
        $objections = $project->objections->first();
        $callTypeValue = $project->call_types->where('id', $data['call_type_id'])->first()->name;
        $heatTypes = Arr::collapse(
            HeatType::all()
                ->transform(function ($item) {
                    return [$item->id => $item->system_name];
                })
        );

        /** @var User $user */
        $user = auth()->user();

        // 1. Find rating with relations
        /** @var CallRating $rating */
        $rating = CallRating::query()
            ->with(['criteria', 'crm', 'objection', 'additionalCriteria'])
            ->where('audio_id', $request->audio_id)
            ->where('project_id', $project->id)
            ->first();

        // 2. Validation for reassessment
        if ($rating) {
            // переоценка невозможна
            throw ValidationException::withMessages(['user_id' => "Звонок уже оценен, переоценка невозможна."]);
//            // check date
//            if (now()->diffInDays($rating->created_at) > 14) {
//                throw ValidationException::withMessages(['created_at' => "После первой оценки прошло более 14 дней"]);
//            }
//            // check roles
//            $firstRatingLog = $rating->firstLog;
//            // first user from logs for new ratings, first user from ratings for old ratings
//            $firstUserId = $firstRatingLog?->user_id ?: $rating->user_id;
//            $assessorsIds = $project->assessors()->pluck('project_assessors.id')->all();
//            if (
//                !(
//                    ($user->isAssessor() && in_array($user->id, $assessorsIds) && ($user->id == $firstUserId)) ||
//                    ($user->isSeniorAssessor() && ($user->id == $project->senior_id)) ||
//                    ($user->isPm() && ($user->id == $project->pm_id)) ||
//                    $user->isAnalytic() ||
//                    $user->isAdmin()
//                )
//            ) {
//                throw ValidationException::withMessages(['user_id' => "У вас недостаточно прав для переоценки"]);
//            }
        }

        DB::transaction(function () use ($data, $project, $objections, $callTypeValue, $heatTypes, &$rating, $user) {

            // 3. Remove old record
            # старая бажная логика. Выше проводится проверка на существование оценки по звонку и проекту
            #if ($rating) {
            #    $rating->delete();
            #}

            // 4. Create a new record
            $rating = new CallRating();
            $rating->user_id = $user->id;
            $rating->project_id = $project->id;
            $rating->audio_id = $data['audio_id'];
            $rating->comments = $data['comments'];
            $rating->call_type_id = $data['call_type_id'];
            $rating->call_type_value = $callTypeValue;
            $rating->heat = $heatTypes[$data['heat']] ?? $data['heat'];
            $rating->type = $data['call_type'];
            $rating->created_date = date('Y-m-d', strtotime($data['date']));
            $rating->created_time = date('H:i:s', strtotime($data['time']));
            $rating->duration = $data['duration'] ? date('H:i:s', strtotime($data['duration'])) : null;
            $rating->audio_link = $data['audio'];
            $rating->link_to_lead = $data['link_to_lead'];
            $rating->manager = $data['manager'];
            $rating->save();

            // 5. Next, save relations: CRITERIA, CRM and OBJECTIONS
            $insertableCriteria = [];
            foreach ($data['criteria'] as $criterion) {
                $insertableCriteria[] = [
                    'criteria_id' => $criterion['id'],
                    'value' => (is_null($criterion['value']) || $criterion['value'] === -1) ?
                        null :
                        ($criterion['value'] === 1 ? '1' : ($criterion['value'] === 0.5 ? '0.5' : '0')),
                ];
            }
            $rating->criteria()->createMany($insertableCriteria);

            $insertableCrmFields = [];
            foreach ($data['crm'] as $datum) {
                $insertableCrmFields[] = [
                    'crm_field_id' => $datum['id'],
                    'value' => [1 => '1', 0 => '0'][$datum['value']] ?? null,
                ];
            }
            $rating->crm()->createMany($insertableCrmFields);

            $insertableObjectionFields = [];
            $insertableObjectionFields[] = [
                'objection_field_id' => $data['objection'] ?? null,
                'value' => $data['objection'] ?? null,
                'google_column' => $objections->google_column,
                'objection_rate' => $data['objection_rate'] ?? null,
                'google_column_rate' => $objections->google_column_rate,
            ];
            $rating->objection()->createMany($insertableObjectionFields);

            // Additional criteria
            $insertableAdditionalCriteria = [];
            foreach ($data['additional_criteria'] as $datum) {
                $insertableAdditionalCriteria[] = [
                    'additional_criteria_id' => $datum['id'],
                    'additional_criteria_option_id' => $datum['option_id'],
                    'value' => Arr::first($datum['options'], function ($item) use ($datum) {
                            return $item['id'] === $datum['option_id'];
                        })['value'] ?? null,
                ];
            }
            $rating->additionalCriteria()->createMany($insertableAdditionalCriteria);
        });

        if (config('services.google.enabled')) {
            SendCallRatingsToGoogleSheets::dispatch($project, $data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Данные об оценке звонка были сохранены успешно',
            'rating' => $rating,
        ]);
    }

    /**
     * @param ValidateRecordRequest $request
     * @param ProjectRepository $pr
     * @param $projectId
     * @param $recordId
     * @return JsonResponse
     * @throws ValidationException
     */
    public function recordLink(ValidateRecordRequest $request, ProjectRepository $pr, $projectId, $recordId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['integration', 'calls' => ['call_id' => $recordId]]);

        if (!$project->hasIntegration()) {
            return response()->json([], 404);
        }

        $integrable = resolve($project->reference->type)
            ->with('token')
            ->where('id', '=', $project->integration->integration_id)
            ->first();

        $response = [];
        if ($project->calls->isEmpty()) {
            throw ValidationException::withMessages(['record' => 'Запись для данного проекта не найдена!']);
        }

        $call = $project->calls[0];

        if (!empty($call) && $call->record_link) {
            if ($project->reference->system_name === 'bitrix_24') {
                if ($request->query('no_download')) {

                    //$response['link'] = $call->record_link . "&auth={$integrable->token->access_token}";
                    $response = [
                        'record_link_origin' => $call->record_link,
                        'link' => $call->record_link . "&auth={$integrable->token->access_token}"
                    ];

                } else {
                    $record = BitrixHelper::request($integrable, [
                        'domain' => $integrable->domain,
                        'method' => 'disk.file.get',
                        'query' => [
                            'id' => $call->record_file_id
                        ]
                    ]);
                    $result = $record->toArray()['data']['result'];
                    $response = [
                        'record_link_origin' => $call->record_link,
                        'link' => $call->record_link . "&auth={$integrable->token->access_token}",
                        'web_download_link' => $this->makeRecordDownloadLink($result),
                        'download_link' => $result['DOWNLOAD_URL'],
                        'filename' => $result['NAME']
                    ];
                }

                return response()->json($response);
            } elseif ($project->reference->system_name === 'amo_crm') {

                //$response['link'] = $call->record_link;
                $response = [
                    'record_link_origin' => $call->record_link,
                    'link' => $call->record_link,
                ];

                return response()->json($response);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Запись не найдена'
        ], 404);
    }

    public function saveCallSettings(ValidateCallSettingsRequest $request, ProjectRepository $pr, $projectId): JsonResponse
    {
        $project = $pr->findById($projectId, null, ['callSettings']);

        if ($project->reference->system_name === 'bitrix_24') {
            $statusesClass = BitrixCallStatus::class;
        } else {
            $statusesClass = AmoCallStatus::class;
        }
        $callStatuses = resolve($statusesClass)
            ->whereIn(
                'id',
                collect($request->input('statuses'))->pluck('value')
            )->get()
            ->pluck('system_name');

        $data['statuses'] = $callStatuses;
        $data['filter_duration_from'] = $request->input('filter_duration_from');
        $data['filter_duration_to'] = $request->input('filter_duration_to');

        DB::transaction(function () use ($data, $project) {
            $project->callSettings()->updateOrCreate(['project_id' => $project->id], $data);
        });

        $pr->saveCallSettingsSalesManagers($project, $request);
        $pr->saveCallSettingsIntegrationPipelines($project, $request->input('pipelines', []));

        return response()->json([
            'status' => 'success',
            'message' => 'Настройки звонков для проекта были сохранены успешно'
        ]);
    }

    public function refreshIntegrationPipelines(ValidateIntegrationCheckRequest $request)
    {
        $reference = RefIntegration::find($request->input('project_type'));

        $integrable = resolve($reference->type)
            ->where('domain', '=', $request->input('integration_domain'))
            ->first();
        $integrableType = Str::studly($reference->system_name);
        $message = "Не удается найти данные интеграции для текущего проекта. Переподключите или переустановите интеграцию в $integrableType!";

        if (empty($integrable)) {
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        }

        try {
            $integrable->refreshIntegrationPipelines();
        } catch (\Exception $e) {
            report($e);
            $integrable->reportConnectionError($integrable, $e->getMessage());

            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json($integrable->integration->pipelines);
    }

    public function loadIntegrationPipelines(ValidateLoadProjectRequest $request, ProjectRepository $pr, $projectId)
    {
        $project = $pr->findById($projectId);

        if (!$project->integration) {
            return response()->json([]);
        }

        return response()->json($project->integration->pipelines);
    }

    public function salesManagers(ValidateIntegrationCheckRequest $request)
    {
        $reference = RefIntegration::find($request->input('project_type'));

        $integrable = resolve($reference->type)
            ->where('domain', '=', $request->input('integration_domain'))
            ->first();
        $integrableType = Str::studly($reference->system_name);
        $message = "Не удается найти данные интеграции для текущего проекта. Переподключите или переустановите интеграцию в $integrableType!";

        if (empty($integrable)) {
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        }

        try {
            $integrable->refreshSalesManagers();
        } catch (\Exception $e) {
            report($e);
            $integrable->reportConnectionError($integrable, $e->getMessage());

            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json($integrable->integration->getSalesManagers());
    }

    public function loadSalesManagers(ValidateLoadProjectRequest $request, ProjectRepository $pr, $projectId)
    {
        $project = $pr->findById($projectId);

        if (!$project->integration) {
            return response()->json([]);
        }

        return response()->json($project->integration->getSalesManagers());
    }

    public function loadCallSettings(ValidateLoadProjectRequest $request, $projectId)
    {
        $callSettings = ProjectCallSetting::where('project_id', $projectId)->first();

        return response()->json($callSettings);
    }

    public function loadCallSettingsSalesManagers(ValidateLoadProjectRequest $request, ProjectRepository $pr, $projectId)
    {
        $callSettingsSalesManagers = ProjectCallSettingsSalesManager::where('project_id', $projectId)
            ->get();

        return response()->json($callSettingsSalesManagers);
    }

    public function loadCallSettingsIntegrationPipelines(ValidateLoadProjectRequest $request, ProjectRepository $pr, $projectId)
    {
        $callSettingsIntegrationPipelines = ProjectCallSettingsIntegrationPipeline::where('project_id', $projectId)
            ->get();

        return response()->json($callSettingsIntegrationPipelines);
    }

    public function loadProjectManagerAssessors(ValidateLoadProjectRequest $request, ProjectRepository $projectRepository, $projectId)
    {
        return response()->json($projectRepository->projectManagerAssessors($projectId));
    }

    public function saveTasksGenerationStatus(ValidateLoadProjectRequest $request, ProjectRepository $projectRepository, $projectId)
    {
        $status = $projectRepository->saveTasksGenerationStatus($projectId);
        if (!$status) {
            return response()->json([
                'message' => 'Необходимо назначить Асессоров на Менеджеров',
                'status' => 'error',
            ], 403);
        }

        return response()->json([
            'message' => 'Статус генерация заданий изменён успешно',
            'status' => 'success',
        ]);
    }

    public function redistributeCalls(ValidateLoadProjectRequest $request, $projectId)
    {
        logger('redistributeCalls', ['projectId' => $projectId]);
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        $integrationSchedule = IntegrationSchedule::whereHas('calls', function ($query) use ($request) {
            return $query->where(DB::raw("created_at::date"), $request->get('start_date'));
        })->first();

        if (!$integrationSchedule) {
            return response()->json([
                'message' => 'Для этой даты ничего не найдено!',
                'status' => 'error',
            ], 404);
        }

        DistributeIntegrationDataByProject::dispatch($integrationSchedule, $projectId);

        return response()->json([
            'message' => 'Перераспределение запущено',
            'status' => 'success',
        ]);
    }


    public function downloadCalls(Request $request, $projectId)
    {
        $validator = Validator::make($request->all(), [
            'date_start' => 'required|date',
            'date_end' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }
        /** @var Project $project */
        $project = Project::query()->findOrFail($projectId);
        $integration = $project->integration;
        if (!$integration) {
            return response()->json(['message' => 'Интеграция для проекта не найдена', 'status' => 'error',], 404);
        }

        $dateStart = Carbon::parse($request->date_start);
        $dateEnd = Carbon::parse($request->date_end) ?: now();
        if ($dateStart->gt($dateEnd)) {
            return response()->json(['message' => 'Дата начала не может быть больше даты окончания', 'status' => 'error',], 404);
        }
        if ($dateStart->diffInDays($dateEnd) > 14) {
            return response()->json(['message' => 'Период не может быть больше двух недель', 'status' => 'error',], 404);
        }

        if ($integration->ref_integration_id == 1) {
            Artisan::call('amo:calls', [
                '--from' => $dateStart->format('Y-m-d'),
                '--to' => $dateEnd->format('Y-m-d'),
                '--amo_code_id' => $integration->integration_id,
            ]);
        }
        if ($integration->ref_integration_id == 2) {
            Artisan::call('bitrix:calls', [
                '--from' => $dateStart->format('Y-m-d'),
                '--to' => $dateEnd->format('Y-m-d'),
                '--bitrix_code_id' => $integration->integration_id,
            ]);
        }

        return response()->json([
            'message' => 'Импорт звонков запущен',
            'status' => 'success',
        ]);
    }


    public function loadResponsiblePersons(ValidateLoadProjectCallsRequest $request, $projectId): JsonResponse
    {
        $project = Project::where('id', $projectId)->firstOrFail();

        $persons = DB::table('calls')
            ->where('integration_id', $project->integration_id)
            ->select('record_responsible_name', 'record_responsible_id')
            ->distinct()
            ->get();

        return response()->json([
            'responsible_persons' => $persons
        ]);
    }

    private function makeRecordDownloadLink(array $file)
    {
        $parsed = parse_url($file['DOWNLOAD_URL']);
        $fullPath = $parsed['scheme'] . '://' . $parsed['host'] . '/disk/downloadFile/' . $file['ID'];
        $params = [
            'ncc' => 1,
            'filename' => $file['NAME'],
        ];
        return $fullPath . '?' . http_build_query($params);
    }
}
