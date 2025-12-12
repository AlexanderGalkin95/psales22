<?php

namespace App\Console\Commands\temp;

use App\Models\GoogleProject;
use App\Models\Project;
use Illuminate\Console\Command;


class GoogleTableIdUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-table-id-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private $allFiles = [];


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Получаем список всех файлов сервисного аккаунта

        $config = config('services.google.service_account_auth');

        $client = new \Google_Client();
        $client->setAuthConfig($config);
        $client->setApplicationName(config('services.google.application_name'));
        $client->setScopes([\Google_Service_Sheets::DRIVE, \Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');

        $drive = new \Google_Service_Drive($client);
        $allFiles = [];
        $pageToken = null;
        do {
            $parameters = ['pageSize' => 100];
            if ($pageToken) {
                $parameters['pageToken'] = $pageToken;
            }
            $filesResponse = $drive->files->listFiles($parameters);

            $files = $filesResponse->getFiles();
            foreach ($files as $file) {
                //$allFiles[] = $file->toSimpleObject();
                $allFiles[] = ['id' => $file->id, 'name' => $file->name];
            }
            $pageToken = $filesResponse->getNextPageToken();
        } while ($pageToken);

        $this->line('найдено файлов: ' . count($allFiles));
        $this->allFiles = $allFiles;

//        $this->confirm('проверить дубликаты?');
//        foreach ($allFiles as $key1 => $file1) {
//            $id1 = $file1['id'];
//            foreach ($allFiles as $key2 => $file2) {
//                $id2 = $file2['id'];
//                if (($id1===$id2) && ($key1 !== $key2)) {
//                    $this->line('дубликат id=' . $id1);
//                }
//            }
//        }


        $this->confirm('обработать проекты?');
        /** @var Project $project */
        foreach (Project::query()->get() as $project) {
            $this->line("Проект {$project->id} {$project->name}");
            $tableName = $project->google_spreadsheet;
            $tableId = $this->findIdByName($tableName);
            if ($tableId) {
                if ($project->google_spreadsheet_id) {
                    if ($tableId === $project->google_spreadsheet_id) {
                        $this->line('таблица найдена, id в проекте совпадает');
                    } else {
                        if ($this->confirm('таблица найдена, id в проекте отличается, переписать?')) {
                            $project->google_spreadsheet_id = $tableId;
                            $project->save();
                        }
                    }
                } else {
                    if ($this->confirm('таблица найдена, id в проекте пустой, заполнить?')) {
                        $project->google_spreadsheet_id = $tableId;
                        $project->save();
                    }
                }
            } else {
                $this->line('таблица не найдена');
            }
        }

        $this->confirm('обработать проекты для ео?');
        /** @var GoogleProject $gproject */
        foreach (GoogleProject::query()->get() as $gproject) {
            $this->line("ПроектЕО {$gproject->id} {$gproject->name}");
            $tableName = $gproject->name;
            $tableId = $this->findIdByName($tableName);
            if ($tableId) {
                if ($gproject->google_spreadsheet_id) {
                    if ($tableId === $gproject->google_spreadsheet_id) {
                        $this->line('таблица найдена, id в проекте совпадает');
                    } else {
                        if ($this->confirm('таблица найдена, id в проекте отличается, переписать?')) {
                            $gproject->google_spreadsheet_id = $tableId;
                            $gproject->save();
                        }
                    }
                } else {
                    if ($this->confirm('таблица найдена, id в проекте пустой, заполнить?')) {
                        $gproject->google_spreadsheet_id = $tableId;
                        $gproject->save();
                    }
                }
            } else {
                $this->line('таблица не найдена');
            }
        }

        return 0;
    }


    private function findIdByName(string $name): ?string
    {
        foreach ($this->allFiles as $file) {
            $fname = $file['name'];
            if ($name === $fname) {
                return $file['id'];
            }
        }
        return null;
    }



}
