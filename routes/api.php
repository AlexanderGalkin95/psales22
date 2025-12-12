<?php

use App\Http\Controllers\API\GoogleProjectsController;
use App\Http\Middleware\ValidateExtension;
use App\Models\GoogleProject;
use App\Services\Google\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProjectsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('test', function () {
//    $p = GoogleProject::query()->find(404);
//    $s = new GoogleService();
//    $s->setSpreadsheetId($p->google_spreadsheet_id)
//        ->setTabName('FG И CRM')
//        ->validate();
//    $r = $s->getDataForDailyReport($p, now()->subDay());
//    return $r;
//});

//Route::get('test', function () {
//    $bitrixCode = \App\Models\BitrixCode::query()->find(24); //aspro
//    $params = [
//        'domain' => $bitrixCode->domain,
//        'method' => 'voximplant.statistic.get',
//        'query' => [
//            'SORT' => 'ID',
//            'ORDER' => 'DESC',
//            'FILTER' => [
//                ">CALL_START_DATE" => now()->subWeek()->toIso8601String(),
//                "<CALL_START_DATE" => now()->toIso8601String(),
//                "CALL_FAILED_CODE" => \App\Models\BitrixCode::CALL_SUCCESS,
//                "HAS_RECORD" => 'Y',
//            ],
//            'start' => 0
//        ]
//    ];
//    (new \App\Services\Bitrix\Helpers\BitrixHelper)->runRequestCalls($bitrixCode, $params, $integrationScheduleId=0);
//});

//Route::get('test', function () {
//    $bitrixCode = \App\Models\BitrixCode::query()->find(27); //pertrubacia
//    $params = [
//        'domain' => $bitrixCode->domain,
//        'method' => 'voximplant.statistic.get',
//        'query' => [
//            'SORT' => 'ID',
//            'ORDER' => 'DESC',
//            'FILTER' => [
//                ">CALL_START_DATE" => \Carbon\Carbon::parse('2024-09-10 00:00:00')->toIso8601String(),
//                "<CALL_START_DATE" => \Carbon\Carbon::parse('2024-09-10 23:59:00')->toIso8601String(),
//                "CALL_FAILED_CODE" => \App\Models\BitrixCode::CALL_SUCCESS,
//                "HAS_RECORD" => 'Y',
//            ],
//            'start' => 0
//        ]
//    ];
//    (new \App\Services\Bitrix\Helpers\BitrixHelper)->runRequestCalls($bitrixCode, $params, $integrationScheduleId=0);
//});


Route::middleware(['xss', ValidateExtension::class])->post('/ext/login', [\App\Http\Controllers\API\ExtensionLogin::class, 'login']);

Route::middleware(['auth:api', 'xss'])->group(function () {
    Route::get('/users', [\App\Http\Controllers\API\UsersController::class, 'list']);
    Route::post('/user', [\App\Http\Controllers\API\UsersController::class, 'create']);
    Route::put('/users/{userId}', [\App\Http\Controllers\API\UsersController::class, 'update']);
    Route::get('/user', [\App\Http\Controllers\API\UsersController::class, 'user']);
    Route::delete('/users/{userId}', [\App\Http\Controllers\API\UsersController::class, 'delete']);

    /*
    | COMPANIES
    */
    Route::prefix('companies')->group(function () {
        Route::get('/', [\App\Http\Controllers\API\CompanyController::class, 'list']);
        Route::get('/{companyId}', [\App\Http\Controllers\API\CompanyController::class, 'company']);
        Route::post('/create', [\App\Http\Controllers\API\CompanyController::class, 'create']);
        Route::put('/{companyId}/update', [\App\Http\Controllers\API\CompanyController::class, 'update']);
        Route::delete('/{companyId}/deactivate', [\App\Http\Controllers\API\CompanyController::class, 'deactivate']);
    });

    // PROJECTS
    Route::get('/projects', [ProjectsController::class, 'list']);
    Route::get('/extension/projects', [ProjectsController::class, 'userProjects']);
    Route::post('/project', [ProjectsController::class, 'create']);
    Route::get('/projects/{projectId}', [ProjectsController::class, 'project']);
    Route::put('/projects/{projectId}', [ProjectsController::class, 'update']);
    Route::post('/projects/{projectId}/criteria', [ProjectsController::class, 'saveCriteria']);
    Route::post('/projects/{projectId}/criteria/additional', [ProjectsController::class, 'saveAdditionalCriteria']);
    Route::post('/projects/{projectId}/call_types', [ProjectsController::class, 'saveCallTypes']);
    Route::post('/projects/{projectId}/settings', [ProjectsController::class, 'saveSettings']);
    Route::get('/projects/{projectId}/settings', [ProjectsController::class, 'settings']);
    Route::post('/projects/{projectId}/crm', [ProjectsController::class, 'saveProjectCrm']);
    Route::post('/projects/{projectId}/objections', [ProjectsController::class, 'saveProjectObjectionFields']);
    Route::get('/projects/{projectId}/calls', [ProjectsController::class, 'loadProjectCalls']);
    Route::get('/projects/{projectId}/call_ratings', [ProjectsController::class, 'loadProjectCallRatings']);
    Route::post('/projects/{projectId}/call_ratings', [ProjectsController::class, 'saveProjectCallRatings']);
    Route::get('/projects/{projectId}/record/{recordId}', [ProjectsController::class, 'recordLink']);
    Route::post('/projects/{projectId}/call_settings', [ProjectsController::class, 'saveCallSettings']);
    Route::post('/projects/integration-pipelines', [ProjectsController::class, 'refreshIntegrationPipelines']);
    Route::get('/projects/{projectId}/call_settings', [ProjectsController::class, 'loadCallSettings']);
    Route::post('/projects/sales-managers', [ProjectsController::class, 'salesManagers']);
    Route::get('/projects/{projectId}/sales-managers', [ProjectsController::class, 'loadSalesManagers']);
    Route::get('/projects/{projectId}/integration-pipelines', [ProjectsController::class, 'loadIntegrationPipelines']);
    Route::get('/projects/{projectId}/call-settings-sales-managers', [ProjectsController::class, 'loadCallSettingsSalesManagers']);
    Route::get('/projects/{projectId}/call-settings-integration-pipelines', [ProjectsController::class, 'loadCallSettingsIntegrationPipelines']);
    Route::post('/projects/{projectId}/import-status', [ProjectsController::class, 'importStatus']);
    Route::get('/projects/{projectId}/manager-assessors', [ProjectsController::class, 'loadProjectManagerAssessors']);
    Route::post('/projects/{projectId}/tasks-generation-status', [ProjectsController::class, 'saveTasksGenerationStatus']);
    Route::post('/projects/{projectId}/redistribute-calls', [ProjectsController::class, 'redistributeCalls']);
    Route::post('/projects/{projectId}/download-calls', [ProjectsController::class, 'downloadCalls']);
    Route::get('/projects/{projectId}/responsible-persons', [ProjectsController::class, 'loadResponsiblePersons']);

    // LOG EVENTS
    Route::get('log-events', [\App\Http\Controllers\API\LogController::class, 'events']);

    // INTEGRATIONS
    Route::post('/integration/check', [App\Http\Controllers\API\IntegrationController::class, 'checkIntegrationAccess'])->name('integration_check');
    Route::post('/integration/calls', [\App\Http\Controllers\API\IntegrationController::class, 'importIntegrationCalls']);

    Route::post('/google/check', [ProjectsController::class, 'checkGoogleSheet']);

    // TASKS
    Route::post('/projects/{projectId}/generate-task', [\App\Http\Controllers\API\TaskController::class, 'generateTask']);
    Route::get('/tasks', [\App\Http\Controllers\API\TaskController::class, 'tasksList']);
    Route::get('/tasks/{taskId}', [\App\Http\Controllers\API\TaskController::class, 'getTask']);
    Route::get('/tasks/{taskId}/history', [\App\Http\Controllers\API\TaskController::class, 'taskHistory']);

    Route::get('/roles', [\App\Http\Controllers\API\DictionariesController::class, 'roles']);
    Route::get('/roles/{dictionaries}', [\App\Http\Controllers\API\DictionariesController::class, 'index']);
    Route::get('/dictionaries/{dictionary}', [\App\Http\Controllers\API\DictionariesController::class, 'index']);
    Route::get('/dictionaries/projects/{projectId}/call_types', [\App\Http\Controllers\API\DictionariesController::class, 'projectCallTypes']);

    Route::post('/extension/register', [App\Http\Controllers\Auth\ExtensionController::class, 'check'])
        ->name('check_extension');

    Route::put('/extension/{extensionId}/action/block', [App\Http\Controllers\API\ExtensionController::class, 'block'])
        ->name('admin.extension.block');

    Route::put('/extension/{extensionId}/action/unblock', [App\Http\Controllers\API\ExtensionController::class, 'unblock'])
        ->name('admin.extension.unblock');

    Route::delete('/extension/{extensionId}/action/reset', [App\Http\Controllers\API\ExtensionController::class, 'reset'])
        ->name('admin.extension.reset');

    Route::put('/extension/{extensionId}/action/remove_duplicates', [App\Http\Controllers\API\ExtensionController::class, 'removeDuplicates'])
        ->name('admin.extension.remove_duplicates');

    Route::get('/amocrm/widgets/project', [\App\Http\Controllers\API\AmoController::class, 'index']);

    Route::post('/amocrm/widgets/store', [\App\Http\Controllers\API\AmoController::class, 'store'])
        ->name('amocrm.widget.store');

    Route::post('/smscode/request', [\App\Http\Controllers\API\SMSController::class, 'requestSmsCode'])
        ->name('smscode.request');

    Route::resource('google-projects', GoogleProjectsController::class)->parameters([
        'google-projects' => 'projectId'
    ]);
    Route::post('/google-projects/{projectId}/send-report', [GoogleProjectsController::class, 'sendReport']);
    Route::delete('/google-projects/{projectId}/destroy', [GoogleProjectsController::class, 'destroy']);
});
