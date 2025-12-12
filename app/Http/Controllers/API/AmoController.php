<?php

namespace App\Http\Controllers\API;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateAmoStoreRequest;
use App\Models\AmoCode;
use App\Models\CallRating;
use App\Models\Criteria;
use App\Models\HeatType;
use App\Models\Project;
use App\Models\ProjectCrmField;
use App\Models\ProjectObjectionField;
use App\Repositories\ProjectRepository;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use App\Services\Google\GoogleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AmoController extends Controller
{
    public function index(Request $request, ProjectRepository $pr): JsonResponse
    {
        $origin = parse_url($request->header('origin'), PHP_URL_HOST);
        $request->merge(['project' => $origin]);
        $validator = Validator::make($request->only('project'), [
            'project'       => 'required|string|exists:amo_codes,domain'
        ]);

        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        DB::beginTransaction();
        $widget_id = AmoCode::where('domain', '=', $origin)->value('id');
        $project = $pr->getProjectByAttribute('amo_code_id', $widget_id);
        $project->load('callRatings');

        DB::commit();

        return response()->json([
            'project' => $project,
            'status' => 'success'
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store(ValidateAmoStoreRequest $request, ProjectRepository $pr): JsonResponse
    {
        DB::beginTransaction();
        try {
            $origin = parse_url($request->header('origin'), PHP_URL_HOST);

            $widget_id = AmoCode::where('domain', '=', $origin)->value('id');
            /** @var Project $project */
            $project = $pr->getProjectByAttribute('amo_code_id', $widget_id);

            $google = new GoogleService();

            $data = [];
            $objections = ProjectObjectionField::where('project_id', $project->id)->first();
            foreach ($request->all() as $key => $value) {
                if (preg_match('/criteria_(\d+)$/', $key, $matches)) {
                    $value['id'] = $matches[1];
                    $value['google_column'] = Criteria::find($matches[1])->google_column;
                    $value['google_column_number'] = CommonHelper::getNumberFromLetters($value['google_column']);
                    $data['criteria'][] = $value;
                } elseif (preg_match('/crm_(\d+)$/', $key, $matches)) {
                    $value['id'] = $matches[1];
                    $value['google_column'] = ProjectCrmField::find($matches[1])->google_column;
                    $value['google_column_number'] = CommonHelper::getNumberFromLetters($value['google_column']);
                    $data['crm'][] = $value;
                } elseif ($key === 'objections_rate') {
                    $value['google_column_number'] = CommonHelper::getNumberFromLetters($objections->google_column_rate);
                    $data[$key] = $value;
                } elseif ($key === 'objections') {
                    $value['google_column'] = $objections->google_column;
                    $value['google_column_number'] = CommonHelper::getNumberFromLetters($value['google_column']);
                    $data[$key] = $value;
                } else {
                    $data[$key] = $value;
                }
            }
            if (isset($data['criteria'])) {
                $data['criteria'] = collect($data['criteria'])
                    ->sortBy('google_column_number')
                    ->toArray();
            }


            if (isset($data['crm'])) {
                $data['crm'] = collect($data['crm'])
                    ->sortBy('google_column_number')
                    ->toArray();
            }
            $google
                ->setSpreadsheetName($project->googleSpreadsheet)
                ->setSpreadsheetId($project->google_spreadsheet_id)
                ->setTabName($project->googleConnection)
                ->saveRecord($data);

            // 1. Find old record with relations
            $callRating = CallRating::with([
                'criteria',
                'crm',
                'objection',
            ])->where('audio_id', $data['id'])
                ->first();

            // 2. Remove old record
            if ($callRating) $callRating->delete();

            // 3. Create a new record

            /* Получаем все типы теплоты и превращаем в массив вида [ 'name' => 'system_name', 'name' => 'system_name', ... ]
             | TODO:: Временное решение пока не внесём изменения в виджет
             */
            $heatTypes = Arr::collapse(
                HeatType::all()
                    ->transform(function ($item) {
                        return [ $item->name => $item->system_name ];
                    })
            );
            $callRating = CallRating::create([
                'user_id' => Auth::id(),
                'project_id' => $project->id,
                'audio_id' => $data['id'],
                'comments' => $data['comments'],
                'call_type_id' => $data['types']['value'],
                'call_type_value' => $data['types']['title'],
                'heat' => $heatTypes[$data['heat']['value']] ?? $data['heat']['value'],
                'type' => $data['call_type'],
                'created_date' => date('Y-m-d', strtotime($data['date'])),
                'created_time' => date('h:i:s', strtotime($data['time'])),
                'duration' => $data['duration'] ? date('H:i:s', strtotime($data['duration'])) : null,
                'audio_link' => $data['audio'],
                'link_to_lead' => $data['link_to_lead'],
                'manager' => $data['manager'],
            ]);

            // 4. Next, save relations: CRITERIA, CRM and OBJECTIONS
            $insertableCriteria = [];
            foreach ($data['criteria'] as $criterion) {
                $insertableCriteria[] = [
                    'criteria_id' => $criterion['id'],
                    'value' => $criterion['title'] ?? null,
                ];
            }
            $callRating->criteria()->createMany($insertableCriteria);

            $insertableCrmFields = [];
            foreach ($data['crm'] as $datum) {
                $insertableCrmFields[] = [
                    'crm_field_id' => $datum['id'],
                    'value' => $datum['title'] ?? null,
                ];
            }
            $callRating->crm()->createMany($insertableCrmFields);

            $insertableObjectionFields = [];
            $insertableObjectionFields[] = [
                'objection_field_id' => $data['objections']['value'] ?? null,
                'value' => $data['objections']['value'] ?? null,
                'google_column' => $objections->google_column,
                'objection_rate' => $data['objections_rate']['value'] ?? null,
                'google_column_rate' => $objections->google_column_rate,
            ];
            $callRating->objection()->createMany($insertableObjectionFields);

            DB::commit();
            $project->load('callRatings');

            return response()->json([
                'status' => 'success',
                'message' => 'Данные об оценке звонка были сохранены успешно',
                'project' => $project,
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            report($exception);
            return response()->json([
                'status' => 'error',
                'message' => 'Данные об оценке звонка не были сохранены',
            ], 400);
        }
    }
}
