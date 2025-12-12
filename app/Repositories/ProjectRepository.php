<?php

namespace App\Repositories;


use App\Models\Project;
use App\Models\ProjectManagerAssessors;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\QueryListTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class ProjectRepository extends BaseRepository
{
    use QueryListTrait;

    public function getProject($id)
    {
        $project = $this->projectQueryBuilder()->where("projects.id", $id)->first();

        if ($project === null) {
            return $this->respProjectNotFound();
        }

        return $this->mapProject($project);
    }

    public function getProjectByAttribute($column, $value = null)
    {
        $project = $this->projectQueryBuilder()->where("projects.$column", $value)->first();
        if ($project === null) {
            return $this->respProjectNotFound();
        }
        return $this->mapProject($project);
    }

    private function projectQueryBuilder(): Builder
    {
        return Project::with([
            'pm' => function ($q) {
                $q->select(
                    'users.name as label',
                    'users.id as value',
                    'users.id',
                );
            },
            'assessors' => function ($q) {
                $q->select(
                    'users.name as label',
                    'users.id as value',
                    'users.id',
                );
            },
            'senior' => function ($q) {
                $q->select(
                    'users.name as label',
                    'users.id as value',
                    'users.id',
                );
            },
            'rating' => function ($q) {
                $q->select(
                    'ref_ratings.name as label',
                    'ref_ratings.system_name',
                    'ref_ratings.id as value',
                    'ref_ratings.id',
                );
            },
            'criteria' => function ($q) {
                $q->select(
                    'criteria.id',
                    'criteria.name as label',
                    'criteria.legend as text',
                    'criteria.google_column',
                    'criteria.index_number',
                    'criteria.project_id'
                );
            },
            'call_types' => function ($q) {
                $q->select(
                    'project_call_types.id',
                    'project_call_types.name',
                    'project_call_types.short_name',
                    'project_call_types.project_id',
                    'project_call_types.rate_crm',
                );
            },
            'integration',
            'crm',
            'objections',
            'additionalCriteria',
        ])
            ->select([
                'projects.id',
                'projects.company_id',
                'projects.integration_id',
                'projects.integration_domain',
                'projects.name',
                'projects.project_type',
                'projects.pm_id',
                'projects.senior_id',
                'projects.rating_id',
                'projects.google_conection as googleConnection',
                'projects.google_spreadsheet as googleSpreadsheet',
                'projects.google_spreadsheet_id',
                'projects.created_at',
                'projects.date_start',
                'projects.total_time_limit',
                'projects.permissible_error',
                'projects.tasks_generation_status'
            ])->withAggregate('reference as project_type_name', 'system_name');
    }

    /**
     * @param Model|object|static $project
     * @return Model|object|ProjectRepository
     */
    private function mapProject($project)
    {
        $project->objections = (object)$project->objections->groupBy('google_column')
            ->mapWithKeys(function ($item, $key) {
                return [
                    'google_column' => $key,
                    'google_column_rate' => $item[0]->google_column_rate,
                    'options' => $item,
                ];
            })->all();
        $project->unsetRelation('objections');

        $project->settings = (object)[];

        return $project;
    }

    /**
     * @deprecated
     */
    public function list($request): array
    {
        $user = $request->user();
        $limit  = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);
        $mapping = [
            'name' => 'projects.name',
            'status' => 'projects.integration_status',
            'company_id'=> 'projects.company_id',
            'pm_name' => 'pm.name',
            'senior_name' => 'senior.name',
            'relation:assessors' => 'name',
            'reference_name' => 'projects.project_type',
        ];
        $query = $this->model
            ->with([
                'assessors' => function ($q) {
                    $q->select('users.name as label', 'users.id as value', 'users.id');
                }
            ])
            ->leftJoin('ref_integrations as ri', 'ri.id', '=', 'projects.project_type')
            ->leftJoin('users as pm', 'pm.id', '=', 'projects.pm_id')
            ->leftJoin('users as senior', 'senior.id', '=', 'projects.senior_id')
            ->select(
                'projects.id',
                'projects.integration_id',
                'projects.name',
                'projects.pm_id',
                'projects.senior_id',
                'projects.integration_domain',
                'projects.project_type',
                'projects.rating_id',
                'projects.company_id',
                'projects.total_time_limit',
                'projects.permissible_error',
                'projects.date_start',
                'projects.integration_status as status',
                'projects.created_at',
                'pm.name as pm_name',
                'senior.name as senior_name',
                'projects.integration_domain',
                'ri.name as reference_name',
            )
            ->when(
                $user->isAn('assessor'),
                function ($query) {
                    $query->whereHas('assessors', function ($q) {
                        $q->where('user_id', '=', Auth::id());
                    });
                }
            )
            ->when(
                $request->has('$filter'),
                $this->applyFilterClosure($request, $mapping)
            )
            ->when(
                $request->has('search'),
                function ($q) use ($request) {
                    return $q->where(function ($q) use ($request) {
                        $q->Where('projects.name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('projects.id', 'ilike', '%' . $request->search . '%')
                            ->orWhere('pm.name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('senior.name', 'ilike', '%' . $request->search . '%')
                            ->orWhereHas('assessors', function ($qq) use ($request) {
                                $qq->where('name', 'ilike', '%' . $request->search . '%');
                            });
                    });
                }
            )->distinct();

        $total = $this->getCount($query);
        $projects = $query->when(
            $request->has('$orderBy'),
            $this->applyOrderByClosure($request, array_merge($mapping, ['reference_name'   => 'ri.name']))
        )
            ->skip($offset)
            ->take($limit)
            ->get()
            ->transform(function ($item) {
                $item->status = $item->status ? 'Да' : 'Нет';
                return $item;
            });

        return [
            'total' => $total,
            'projects' => $projects
        ];
    }

    public function saveCallSettingsSalesManagers(Project $project, Request $request)
    {
        $salesManagers = $request->input('sales_managers', []);
        $managerAssessors = $request->input('manager_assessors', []);

        DB::transaction(function () use ($salesManagers, $managerAssessors, $project) {
            $project->callSettingsSalesManagers()->delete();

            collect($salesManagers)->each(function ($item) use ($project, $managerAssessors) {
                $salesManager = $project->callSettingsSalesManagers()->save(
                    $project->callSettingsSalesManagers()
                        ->make(array_merge($item, ['project_id' => $project->id]))
                );

                $assessors = collect($managerAssessors)->where('manager_id', '=', $salesManager->sales_manager_id)->collapse();

                $this->saveManagerAssessors($salesManager, $assessors['assessors']);
            });
        });
    }

    public function saveCallSettingsIntegrationPipelines(Project $project, array $pipelines)
    {
        DB::transaction(function () use ($pipelines, $project) {
            $project->callSettingsIntegrationPipelines()->delete();
            $pipelinesSettings = $project->callSettingsIntegrationPipelines()
                ->saveMany(
                    $project->callSettingsIntegrationPipelines()
                        ->makeMany(
                            array_map(function ($item) use ($project) {
                                return array_merge($item, ['project_id' => $project->id]);
                            }, $pipelines)
                        )
                );
            collect($pipelinesSettings)->each(function ($pipeline) use ($pipelines) {
                $filtered = Arr::collapse(
                    Arr::where(
                        $pipelines,
                        function ($value, $key) use ($pipeline) {
                            return $value['integration_pipeline_id'] === $pipeline->integration_pipeline_id;
                        }
                    )
                );
                if (!empty($filtered)) {
                    $this->saveCallSettingsIntegrationPipelineStatuses($pipeline, $filtered['statuses']);
                }
            });
        });
    }

    public function saveCallSettingsIntegrationPipelineStatuses($pipeline, array $statuses)
    {
        $pipeline->selectedStatuses()->delete();
        $pipeline->selectedStatuses()
            ->saveMany(
                $pipeline->selectedStatuses()
                    ->makeMany(
                        array_map(function ($item) {
                            return array_merge($item, ['integration_pipeline_status_id' => $item['id']]);
                        }, $statuses)
                    )
            );
    }

    public function saveManagerAssessors($manager, $assessors)
    {
        $manager->assessors()->saveMany(
            $manager->assessors()->makeMany(
                array_map(function ($item) use ($manager) {
                    return [
                        'user_id' => $item['id'],
                        'project_id' => $manager->project_id,
                    ];
                }, $assessors)
            )
        );
    }

    public function  projectManagerAssessors($projectId)
    {
        return ProjectManagerAssessors::with([
            'assessor' => function ($query) {
                $query->select('id as id', 'id as value', 'name as label');
            }
        ])->where('project_id', $projectId)
            ->get();
    }

    public function saveTasksGenerationStatus($projectId)
    {
        $project = $this->findById($projectId);
        $countEmptyAssessors = $project->callSettingsSalesManagers()->whereDoesntHave('assessors')->count();

        if ($countEmptyAssessors) {
            return false;
        }

        return $project->toggleTasksGenerationStatus();
    }

    public function model(): string
    {
        return Project::class;
    }
}
