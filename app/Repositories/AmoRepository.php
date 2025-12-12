<?php

namespace App\Repositories;

use App\Models\Integration;
use App\Models\Project;
use App\Models\RefIntegration;
use App\Models\ProjectCall;
use App\Notifications\AmoCRMConnectionErrorNotification;
use App\Repositories\Eloquent\BaseRepository;
use App\Services\AmoCRM\Helpers\NotificationReports;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use App\Traits\QueryListTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\AmoCRM\Facades\AmoCRMHelper;

class AmoRepository extends BaseRepository
{
    use QueryListTrait;

    public function saveIntegration()
    {
        $this->model->fresh();
        $reference = RefIntegration::where('type', '=', $this->model())->first();
        $integration = Integration::updateOrCreate([
            'integration_id' => $this->model->id,
            'ref_integration_id' => $reference->id,
        ], [
            'integration_id' => $this->model->id,
            'ref_integration_id' => $reference->id,
        ]);

        $this->bindProjects();

        return $integration;
    }

    public function bindProjects()
    {
        $projects = Project::where('integration_domain', $this->model->domain);
        $projectIds = $projects->pluck('id')->toArray();
        $projects->whereIn('id', $projectIds)->update(['integration_id' => $this->model->integration->id]);
    }

    public function reportConnectionError($error)
    {
        if (!$this->model->id) return;

        $this->model->integration->projects->each(function ($project) {
            $project->update(['integration_status' => false]);
            if ($project->pm) {
                $project->pm->notify(
                    new AmoCRMConnectionErrorNotification($this->model, $project->name)
                );
            }
        });

        $reportableData = new AmoCRMReportable($this->model->domain, $error);

        $message = "Связь с {$this->model->name} потеряна.
                    Сервер не смог обновить код для интеграции в {$this->model->name} c доменом {$this->model->domain}";
        NotificationReports::amoCRMReportToAdmins([$reportableData], $message);
    }

    public function loadCalls($request, $projectId): array
    {
        $limit  = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);

        $mapping = [
            'record_created_at' => DB::raw('record_created_at::timestamp::date'),
            'record_duration' => DB::raw('record_duration::time'),
            'record_event_type' => 'record_event_type',
            'event' => 'record_responsible_id',
            'status' => DB::raw("CASE WHEN cr.audio_id IS NULL THEN 0 ELSE 1 END"),
            'result' => 'acs.id',
        ];

        $query = ProjectCall::join('calls', 'calls.id', '=', 'project_calls.call_id')
            ->leftJoin('call_ratings as cr', function($q) {
                $q->on('cr.audio_id', '=', 'calls.record_id')
                    ->on('project_calls.project_id', '=', 'cr.project_id');
            })
            ->leftJoin("ref_amo_call_statuses as acs", 'acs.system_name', '=', 'calls.record_status')
            ->select([
                'calls.*',
                'project_calls.project_id',
                'acs.name as result',
                DB::raw("CASE WHEN cr.audio_id IS NULL THEN 0 ELSE 1 END AS status"),
            ])
            ->when(
                $request->has('$filter'),
                $this->applyFilterClosure($request, $mapping)
            )->when(
                $request->has('$orderBy'),
                $this->applyOrderByClosure(
                    $request,
                    array_merge($mapping, ['record_created_at' => 'record_created_at', 'result' => 'acs.name'])
                )
            )->when(
                $request->has('search'),
                function ($q) use ($request) {
                    return $q->where(function (Builder $query) use ($request) {
                        $query->where(DB::raw('record_created_at::timestamp::date'), 'ilike', '%' . $request->search . '%')
                            ->orWhere('acs.name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('record_duration', 'ilike', '%' . $request->search . '%')
                            ->orWhere('record_element_name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('record_responsible_name', 'ilike', '%' . $request->search . '%');
                    });
                }
            )
            ->where('project_calls.project_id', $projectId);

        $total = $this->getCount($query);
        $calls = $query->skip($offset)
            ->take($limit)
            ->get()
            ->transform(function ($item) {
                $item->event = [
                    'outbound' => "$item->record_responsible_name на $item->record_element_name",
                    'inbound' => "$item->record_element_name на $item->record_responsible_name",
                ][$item->record_event_type];

                return $item;
            });

        return [$total, $calls];
    }

    public function refreshSalesManagers($amoCode, int $scheduleId)
    {
        try {
            AmoCRMHelper::runRequestSalesManagers(
                $amoCode,
                [
                    'filter' => [
                        'with' => 'role,group',
                        'page' => 1,
                        'limit' => 20,
                    ]
                ],
                $scheduleId
            );
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function refreshIntegrationPipelines($amoCode, int $scheduleId)
    {
        try {
            AmoCRMHelper::runRequestPipelines($amoCode, [], $scheduleId);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function model(): string
    {
        return \App\Models\AmoCode::class;
    }
}
