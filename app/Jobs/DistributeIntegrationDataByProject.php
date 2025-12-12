<?php

namespace App\Jobs;

use App\Models\Call;
use App\Models\Project;
use App\Models\IntegrationSchedule;
use App\Models\ProjectCall;
use Carbon\Carbon;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DistributeIntegrationDataByProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;

    public IntegrationSchedule $integrationSchedule;

    public ?int $projectId = null;


    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return int
     */
    public function backoff(): int
    {
        return 60;
    }


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(IntegrationSchedule $integrationSchedule, ?int $projectId = null)
    {
        $this->onQueue('distributing');
        $this->integrationSchedule = $integrationSchedule;
        $this->projectId = $projectId;
    }


    private function logger()
    {
        return Log::channel('distributing');
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->logger()->info('Run distribution process.');
        $this->logger()->info('IntegrationSchedule type: ' . $this->integrationSchedule->type);

        $this->setIsRunning();

        if ($this->integrationSchedule->type === IntegrationSchedule::SCHEDULE_TYPE_CALLS) {
            $this->distributeCalls();
        }

        $this->setIsCompleted();
    }

    protected function setIsRunning()
    {
        $this->logger()->info('Распределение звонков начато::' . $this->integrationSchedule->id);
        if ($this->integrationSchedule->status === IntegrationSchedule::SCHEDULE_STATUS_CREATED) {
            $payload = [
                'status' => IntegrationSchedule::SCHEDULE_STATUS_RUNNING,
                'runtime' => DB::raw('now()')
            ];
            $this->integrationSchedule->update($payload);
            $this->logger()->info('IntegrationSchedule update completed');
        }
    }

    protected function setIsCompleted(): void
    {
        $this->integrationSchedule->status = IntegrationSchedule::SCHEDULE_STATUS_COMPLETED;
        $this->integrationSchedule->save();
        $this->logger()->info('Распределение звонков окончено::' . $this->integrationSchedule->id);
    }

    protected function setIsFailing(): void
    {
        $this->integrationSchedule->status = IntegrationSchedule::SCHEDULE_STATUS_FAILED;
        $this->integrationSchedule->save();
        $this->logger()->info('Распределение звонков провалилось::' . $this->integrationSchedule->id);
    }

    public function failed(Throwable $exception): void
    {
        $this->logger()->error($exception->getMessage());
        $this->setIsFailing();
    }

    protected function distributeCalls(): void
    {
        $this->logger()->info('DistributeCalls started');

        $this->logger()->info('project:' . $this->projectId);

        $projects = Project::with('integration')
            ->whereHas('integration')
            ->when(
                $this->projectId,
                function ($query) {
                    $query->where('projects.id', '=', $this->projectId);
                }
            )
            ->get();
        $this->logger()->info('Projects has been loaded. Count:  ' . count($projects));
        $projects->each(function ($project) {
            $this->logger()->info('Project:  ' . $project->id);
            $calls = Call::where([
                'schedule_id' => $this->integrationSchedule->id,
                'integration_id' => $project->integration_id,
            ])
                ->where($this->mapProjectFilters($project))
                ->get()
                ->transform(function ($item) use ($project) {
                    return [
                        'call_id' => $item->id,
                        'project_id' => $project->id
                    ];
                })
                ->toArray();

            $chunks = array_chunk($calls, 1000, true);
            foreach ($chunks as $chunk) {
                ProjectCall::upsert(
                    $chunk,
                    ['call_id', 'project_id']
                );
            }

            $projectsMinDateStart = $project->integration->projectsMinDateStart();

            switch ($project->reference->system_name) {
                case 'bitrix_24':
                    $command = 'bitrix:calls';
                    break;
                case 'amo_crm':
                    $command = 'amo:calls';
                    break;
                default:
                    return;
            }
            $this->logger()->info('Project ' . $project->id . ' projectsMinDateStart:  ' . $projectsMinDateStart);
            if ($projectsMinDateStart) {
                if (!$project->integration->min_date_start) {
                    $this->logger()->info('Command call: ' . "$command --from=$projectsMinDateStart");
                    Artisan::call("$command --from=$projectsMinDateStart");
                } elseif ($projectsMinDateStart < $project->integration->min_date_start) {
                    $this->logger()->info('Command call: ' . "$command --from={$project->integration->min_date_start} --to=$projectsMinDateStart");
                    Artisan::call("$command --from={$project->integration->min_date_start} --to=$projectsMinDateStart");
                }
                $this->logger()->info('Command call: ' . $command);

                $project->integration->min_date_start = Carbon::parse("$projectsMinDateStart")->format('Y-m-d');
                $project->integration->save();
            }
            $this->logger()->info('Project ' . $project->id . ' done!');
        });
    }

    private function mapProjectFilters($project): Closure
    {
        return function ($query) use ($project) {
            $callSettings = $project->callSettings;
            if ($callSettings) {
                $statuses = $callSettings->statuses;
                if (!empty($filtered)) {
                    $query->whereIn('record_status', array_values($statuses));
                }
                if ($callSettings->filter_duration_from) {
                    $time = $this->secondsToTime($callSettings->filter_duration_from);
                    $query->where(DB::raw('record_duration::time'), '>=', $time);
                }
                if ($callSettings->filter_duration_to) {
                    $time = $this->secondsToTime($callSettings->filter_duration_to);
                    $query->where(DB::raw('record_duration::time'), '<=', $time);
                }
            }

            // Добавляем фильтры для продажников на прослушку
            $callSettingsSalesManagers = $project->callSettingsSalesManagers;
            if ($callSettingsSalesManagers->count()) {
                $managersIds = $callSettingsSalesManagers->pluck('salesManager.foreign_manager_id')->all();
                $query->whereIn('record_responsible_id', $managersIds);
            }

            return $query;
        };
    }

    private function secondsToTime(int $seconds): string
    {
        return gmdate('H:i:s', $seconds);
    }
}
