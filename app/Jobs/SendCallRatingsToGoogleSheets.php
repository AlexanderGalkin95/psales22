<?php

namespace App\Jobs;

use App\Helpers\CommonHelper;
use App\Models\HeatType;
use App\Models\Project;
use App\Services\Google\GoogleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class SendCallRatingsToGoogleSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data = [];

    public Project $project;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Project $project, array $data)
    {
        $this->project = $project;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $googleService = new GoogleService();
        $googleService
            ->setSpreadsheetName($this->project->google_spreadsheet)
            ->setSpreadsheetId($this->project->google_spreadsheet_id)
            ->setTabName($this->project->google_conection)
            ->saveRecord($this->prepareForGoogleSheets($this->data));
    }

    private function prepareForGoogleSheets(array $data)
    {
        $objections = $this->project->objections->first();
        $callType = $this->project->call_types->where('id', $data['call_type_id'])->first();
        $heatTypes = HeatType::all()->toArray();

        // $data['time'] = gmdate('H:i:s', strtotime($data['time']));
        $data['types']['title'] = $callType->name;
        $data['types']['value'] = $data['call_type_id'];
        $heat = Arr::first($heatTypes, function ($item) use ($data) {
            return $item['id'] === $data['heat'];
        });
        $data['heat'] = [];
        $data['heat']['value'] = $heat['name'] ?? $data['heat'];
        $data['call_type'] = ['outbound' => 'Исходящий', 'inbound' => 'Входящий'][$data['call_type']];

        foreach ($data['criteria'] as &$datum) {
            $datum['google_column_number'] = CommonHelper::getNumberFromLetters($datum['google_column']);
            $datum['title'] = (is_null($datum['value']) || $datum['value'] === -1) ?
                null : ($datum['value'] === 1 ? '1' :
                ($datum['value'] === 0.5 ? '0,5' : '0'));
        }

        foreach ($data['crm'] as &$datum) {
            $datum['google_column_number'] = CommonHelper::getNumberFromLetters($datum['google_column']);
            $datum['value'] = $datum['value'] ?? null;
        }

        $objection = $objections->firstWhere(['id' => $data['objection']]);
        $data['objections'] = [
            'title' => $objection ? $objection->name : null,
            'value' => $data['objection'] ?? null,
            'google_column_number' => CommonHelper::getNumberFromLetters($objections->google_column)
        ];
        $data['objections_rate'] = [
            'value' => $data['objection_rate'] ?? null,
            'google_column_number' => CommonHelper::getNumberFromLetters($objections->google_column_rate)
        ];

        foreach ($data['additional_criteria'] as &$datum) {
            $option = Arr::first($datum['options'], function ($item) use ($datum) {
                return $item['id'] === $datum['option_id'];
            });
            $datum['google_column_number'] = '';
            $datum['title'] = $option['label'] ?? null;
            $datum['value'] = $option['value'] ?? null;
        }

        $assessor = Auth::user();
        if ($assessor) {
            $data['assessor'] = $assessor->name;
        }

        $data['link_to_lead'] = "https://{$this->project->integration_domain}{$data['link_to_lead']}";

        return $data;
    }
}
