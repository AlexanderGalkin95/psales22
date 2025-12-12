<?php

namespace App\Services\Google;

use App\Helpers\CommonHelper;
use App\Models\DailyReport;
use App\Models\GoogleProject;
use App\Services\Google\Exceptions\GoogleSaveDataException;
use App\Services\Google\Exceptions\GoogleServiceException;
use App\Services\Google\Exceptions\GoogleSpreadsheetException;
use App\Services\Google\Exceptions\GoogleWorksheetException;
use Carbon\Carbon;
use Exception;
use Google_Client;
use Google\Model as Google_Model;
use Google_Service_Drive;
use Google_Service_Sheets;
use Google_Service_Sheets_UpdateValuesResponse;
use Google_Service_Sheets_ValueRange;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GoogleService
{
    protected Google_Client $client;

    protected array $accessToken;

    protected ?string $spreadsheetName = '';

    protected ?string $spreadsheetId = '';

    protected ?string $tabName = '';

    protected array $data;

    protected int $rowCount = 0;

    protected $sheets = null;

    protected ?string $currentSheet = '';

    private string $valueRenderOption = 'FORMATTED_VALUE';

    private string $dateTimeRenderOption = 'FORMATTED_STRING';

    protected Google_Service_Sheets $service;

    protected array $ratingTitleRaw = [];

    const FG_DAILY = 'FG Daily';

    const CRM = 'CRM';

    const DATES_ROW = 'Менеджер';

    const MONTH_ROW = 'Месяц';

    const COMMENT_COLUMN = 'Комментарий';

    const DEAL_COLUMN = 'Сделка';

    const CALL_COLUMN = 'Звонок';

    const GRADE_COLUMN = 'Оценка';

    const MANAGER_COLUMN = 'Менеджер';

    const COLUMNS_NAMES_ROW_RANGE = 'Оценки!2:2';

    const DATES_COLUMN_RANGE = 'Оценки!A:A';

    const TOP_GRADE = 'ТОП';
    const GOOD_GRADE = 'ХОРОШО';
    const PARSE_GRADE = 'Для разбора';
    const FAILED_GRADE = 'Слит';
    const ERROR_CRM_GRADE = 'ОШИБКА CRM';
    const OBJECTION_GRADE = 'Возражение';

    /**
     * @throws Exception
     */
    public function __construct(string $tabName = '', $data = [])
    {
        try {
            $this->setSpreadsheetName(config('services.google.spreadsheet'));
            $this->setTabName($tabName);
            $this->setData($data);
            $this->setClient();
        } catch (Exception $e) {
            throw new GoogleServiceException($e->getMessage());
        }
    }

    public function setSpreadsheetId($spreadsheetId): GoogleService
    {
        $this->spreadsheetId = $spreadsheetId;
        return $this;
    }

    public function setSpreadsheetName($spreadsheet): GoogleService
    {
        $this->spreadsheetName = $spreadsheet;
        return $this;
    }

    public function getSpreadsheetNameShort(): string
    {
        if ($this->spreadsheetName) {
            return $this->spreadsheetName;
        } elseif ($this->spreadsheetId) {
            return Str::substr($this->spreadsheetId, 0, 20) . '...';
        }
        return '<название не задано>';
    }

    public function setTabName($tabName): GoogleService
    {
        $this->tabName = $tabName;

        return $this;
    }

    public function setData($data): GoogleService
    {
        $this->data = $data;

        return $this;
    }

    private function setClient(): void
    {
        //putenv('GOOGLE_APPLICATION_CREDENTIALS=' . base_path() . '/service-credentials.json');
        $config = config('services.google.service_account_auth');

        $client = new Google_Client();
        $client->setAuthConfig($config);
        //$client->useApplicationDefaultCredentials();
        $client->setApplicationName(config('services.google.application_name'));
        $client->setScopes([Google_Service_Sheets::DRIVE, Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');

        $this->client = $client;
    }

    public function authenticate()
    {
        $this->client->fetchAccessTokenWithAssertion();

        $this->accessToken = $this->client->getAccessToken();

        $this->setService();
    }

    protected function setService()
    {
        $this->service = new Google_Service_Sheets($this->client);
    }

    /**
     * @throws Exception
     */
    public function validate(): bool
    {
        // must set (spreadsheetId or spreadsheetName) and tabName
        if ((!$this->spreadsheetId && !$this->spreadsheetName) || !$this->tabName) {
            throw new Exception('Not enough required data');
        }

        $this->authenticate();

        // если идентификатор файла не задан, ищем его по имени
        if (!$this->spreadsheetId) {
            $drive = new Google_Service_Drive($this->client);
            $allFiles = [];
            $pageToken = null;
            do {
                $parameters = ['pageSize' => 100];
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $filesResponse = $drive->files->listFiles($parameters);
                $allFiles = array_merge($allFiles, $filesResponse->getFiles());

                $pageToken = $filesResponse->getNextPageToken();
            } while ($pageToken);
            $_spreadsheet = collect($allFiles)->transform(function ($file) {
                return $file->toSimpleObject();
            })->where('name', $this->spreadsheetName)->first();
            if (empty($_spreadsheet)) {
                throw new GoogleSpreadsheetException(
                    'Таблица ' . $this->spreadsheetName . ' не найдена в Google Drive, проверьте существование таблицы и права на чтение и запись.');
            }
            $this->setSpreadsheetId($_spreadsheet->id);
        }

        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);

        if (empty($spreadsheet)) {
            throw new GoogleSpreadsheetException(
                'Таблица ' . $this->getSpreadsheetNameShort() . ' не найдена в Google Drive, проверьте существование таблицы и права на чтение и запись.');
        }

        $this->sheets = $spreadsheet->getSheets();
        $worksheet = array_filter($this->sheets, function ($sheet) {
            return $sheet->getProperties()->getTitle() === $this->tabName;
        });
        if (empty($worksheet)) {
            throw new GoogleWorksheetException(
                "Лист '{$this->getTabName()}' в таблице '{$this->getSpreadsheetNameShort()}' не найден. Найденные листы: " .
                collect($this->sheets)->transform(fn($sheet) => $sheet->getProperties()->getTitle())->join(', ')
            );
        }

        return true;
    }

    /**
     * @return null
     */
    public function getSheets()
    {
        return $this->sheets;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * @throws Exception
     */
    public function saveRecord($data = [])
    {
        if (count($data)) {
            $this->setData($data);
        }

        if (!count($this->data)) {
            return;
        }

        try {
            $this->validate();

            $this->buildInsertBody('USER_ENTERED');
        } catch (Exception $exception) {
            throw new GoogleSaveDataException($exception->getMessage());
        }
    }

    public function getDataForDailyReport(GoogleProject $project, $date)
    {
        try {
            return $this->builDailyReportRequest($project, $date);
        } catch (Exception $exception) {
            $newException = new GoogleSaveDataException(message: $exception->getMessage(), previous: $exception);
            throw $newException;
        }
    }

    /**
     * @throws Exception
     */
    protected function buildInsertBody($valueInputOption): Google_Service_Sheets_UpdateValuesResponse
    {
        $data = $this->mapDataForSaving();

        $range = $this->getRange();
        return $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            new Google_Service_Sheets_ValueRange([
                'majorDimension' => 'ROWS',
                'values' => [...$data],
            ]),
            ['valueInputOption' => $valueInputOption]
        );
    }

    private function getRange(): string
    {
        $start = $this->getFirstEmptyRowForInsert();
        return sprintf('%s!A%s', $this->getTabName(), ++$start);
    }

    /**
     * @throws Exception
     */
    protected function builDailyReportRequest($project, $date): array
    {
        $ranges = [
            "report" => "$this->tabName",
        ];

        $column = $this->getTitleColumnAlfa('Критерии', $date->format('d.m.Y'), 5);
        $ranges["criterias"] = "Критерии!C6:$column";
        $ranges["column_names"] = self::COLUMNS_NAMES_ROW_RANGE;

        $allDates = $this->service->spreadsheets_values->get($this->spreadsheetId, self::DATES_COLUMN_RANGE, [
            'valueRenderOption' => 'UNFORMATTED_VALUE',
        ])->getValues();
        $targetDates = array_filter($allDates, function ($item) use ($date) {
            if (isset($item[0])) {
                return $this->convertGoogleSheetsDate($item[0])->format('d.m.Y') === $date->format('d.m.Y');
            }
            return false;
        });
        $targetDatesIndexes = array_keys($targetDates);
        foreach ($targetDatesIndexes as $key) {
            $ranges["row_data" . $key + 1] = 'Оценки!' . $key + 1 . ':' . $key + 1;
        }

        /*
        В массиве $ranges находятся диапазоны, которые будем запрашивать из Гугл таблицы.
        Порядок ответа ($response) такой же, как порядок запрошенных диапазонов.
        https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/batchGet?hl=ru
        Пример массива $ranges
        [
            "report" => "FG И CRM"
            "criterias" => "Критерии!C6:SD"
            "column_names" => "Оценки!2:2"
            "row_data3" => "Оценки!3:3"
            "row_data300" => "Оценки!300:300"
            "row_data500" => "Оценки!500:500"
            "row_data705" => "Оценки!705:705"
            "row_data706" => "Оценки!706:706"
            "row_data721" => "Оценки!721:721"
            ...
        ]
        */

        $response = $this->service->spreadsheets_values->batchGet($this->spreadsheetId, [
            'ranges' => $ranges,
            'majorDimension' => 'ROWS',
        ])->getValueRanges();
//dd($response);
        $ratingRangesData = array_slice($response, 3);
        $filteredRatingRangesData = array_map(function ($item) {
            if (isset($item->getValues()[0])) {
                return $item->getValues()[0];
            }
            return false;
        }, $ratingRangesData);
//dd($filteredRatingRangesData);
        if (!isset($response[0]['values'])) {
            throw new GoogleServiceException('В проекте ' . $project->name . ' id: ' . $project->id . ', данные в листе "FG И CRM" не найдены');
        }
        if (!isset($response[1]['values'])) {
            throw new GoogleServiceException('В проекте ' . $project->name . ' id: ' . $project->id . ', данные в листе "Критерии" не найдены');
        }
        if (isset($response[2]) && isset($response[2]->getValues()[0])) {
            $columnsNames = $response[2]->getValues()[0];
        } else {
            throw new GoogleServiceException('В проекте ' . $project->name . ' id: ' . $project->id . ', строка c названием колонок в листе "Оценки" не найдена');
        }
//dd($response[0]->getValues());
        return [
            'report' => $this->parseReports($response[0]->getValues(), $project, $date),
            'rating' => $this->parseRating($filteredRatingRangesData, $columnsNames, $project),
            'criterias' => $this->parseCriterias($response[1]->getValues(), $project),
        ];
    }

    protected function parseReports($reports, $project, $date): array
    {
        $datesIndex = null;
        foreach ($reports as $key => $report) {
            if (in_array(self::DATES_ROW, $report)) {
                $datesIndex = $key;
                break;
            }
        }
        $allDates = $reports[$datesIndex];
        $targetDateIndex = array_search($date->format('d.m.Y'), $allDates) ?: array_search($date->day, $allDates);

        $data = [];
        $crmIndex = collect($reports)->search(fn($value, $key) => ($value[0] ?? '') === self::CRM);
        if (empty($crmIndex)) {
            return $data;
        }
        $fgs = $this->filterParsedReports(array_slice($reports, 0, $crmIndex));
        $crm = $this->filterParsedReports(array_slice($reports, $crmIndex + 1));
//dd($fgs);
        foreach ($fgs as $key => $value) {
            $fg = $targetDateIndex ? $value[$targetDateIndex] : null;
            $c = $targetDateIndex ? $crm[$key][$targetDateIndex] : null;
            $data[] = [
                'project_id' => $project->id,
                'manager' => $value[0],
                'fg' => $fg !== '-' ? $fg : null,
                'crm' => $c !== '-' ? $c : null,
            ];
        }
        return $data;
    }

    protected function parseCriterias($criterias, $project)
    {
        $range = $this->getRowRangeWhere('Критерии!A6:A', 'TRUE');
        $filtered = Arr::where($criterias, function ($item, $key) use ($range) {
            if (empty($range)) {
                return false;
            }
            return in_array($key, $range);
        });
        return collect($filtered)->transform(function ($item) {
            return [$item[0], trim(Arr::last($item), '%')];
        });
    }

    /**
     * @throws Exception
     */
    protected function parseRating($rating, $columnsNames, $project): Collection
    {
        $managerNameColumnIndex = $this->getColumnIndex(self::MANAGER_COLUMN, $columnsNames, $project);
        $commentColumnIndex = $this->getColumnIndex(self::COMMENT_COLUMN, $columnsNames, $project);
        $dealColumnIndex = $this->getColumnIndex(self::DEAL_COLUMN, $columnsNames, $project);
        $callColumnIndex = $this->getColumnIndex(self::CALL_COLUMN, $columnsNames, $project);
        $gradeColumnIndex = $this->getColumnIndex(self::GRADE_COLUMN, $columnsNames, $project);

//        $filtered = array_filter($rating, fn($item) => $item[$gradeColumnIndex] === self::TOP_GRADE
//            || $item[$gradeColumnIndex] === self::PARSE_GRADE
//            || $item[$gradeColumnIndex] === self::FAILED_GRADE
//        );
        $filtered = array_filter($rating, function ($item) use ($gradeColumnIndex) {
            $grade = $item[$gradeColumnIndex] ?? '';
            return in_array($grade, [self::TOP_GRADE, self::GOOD_GRADE, self::PARSE_GRADE, self::FAILED_GRADE, self::ERROR_CRM_GRADE, self::OBJECTION_GRADE]);
        });

        return collect(array_values($filtered))
            ->transform(fn($item) => [
                $item[$managerNameColumnIndex] ?? '',
                $item[$commentColumnIndex] ?? '',
                $item[$dealColumnIndex] ?? '',
                $item[$callColumnIndex] ?? '',
                $item[$gradeColumnIndex] ?? '',
            ]);
    }

    protected function trimArray($array, string $needle = '')
    {
        $trimIndex = collect($array)->search(fn($value, $key) => !count($value) || $value[0] === $needle);
        if ($trimIndex) {
            return array_slice($array, 0, $trimIndex);
        }
        return $array;
    }

    public function saveReport(array $reports = [])
    {
        return DailyReport::upsert($reports, ['project_id', 'manager', 'created_at']);
    }

    protected function getFirstEmptyRowForInsert(): int
    {
        $values = $this->service->spreadsheets_values
            ->get($this->spreadsheetId, "$this->tabName!A:A")
            ->getValues();
        $row = 1;
        array_shift($values);
        foreach ($values as $value) {
            if (empty($value)) {
                break;
            }
            $row++;
        }

        return $row;
    }

    private function getWholeRange(string $range): array
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range, [
            'valueRenderOption' => $this->valueRenderOption,
            'dateTimeRenderOption' => $this->dateTimeRenderOption
        ]);
        return $response->getValues();
    }

    public function getTitleColumnAlfa(string $sheet, string $title, int $titleIndex)
    {
        if ($sheet !== $this->currentSheet || !count($this->ratingTitleRaw)) {
            $this->currentSheet = $sheet;
            $response = $this->service->spreadsheets_values
                ->get($this->spreadsheetId, "$this->currentSheet!A$titleIndex:ZZ$titleIndex")
                ->getValues();
            $this->ratingTitleRaw = $response[0];
        }
        $index = array_search($title, $this->ratingTitleRaw);
        if ($index) {
            return CommonHelper::numberToAlpha($index);
        }
        return CommonHelper::numberToAlpha(497);
    }

    public function getRowRangeWhere(string $range, $value)
    {
        $values = Arr::collapse($this->getWholeRange("$range"));
        $range = array_keys(array_filter($values, function ($v) use ($value) {
            return $v === $value;
        }));

        if (!count($range)) {
            return null;
        }
        return $range;
    }

    /**
     * @throws Exception
     */
    protected function mapDataForSaving(): array
    {
        $mappedData = [];

        $data = $this->data;

        $mapped = [];
        $mapped[] = $data['date'];
        // Время
        $mapped[] = $data['time'];
        // Длина
        $mapped[] = $data['duration'] ?? Google_Model::NULL_VALUE;
        // Менеджер
        $mapped[] = $data['manager'];
        // И/В
        $mapped[] = $data['call_type'];
        // ТИП
        $mapped[] = $data['types']['title'];
        // Теплота
        $mapped[] = $data['heat']['value'];

        // Сохраняем критерии
        $currentColumnNumber = 8;
        if ($length = count($data['criteria'] ?? [])) {
            foreach ($data['criteria'] as $criterion) {
                $skip = $criterion['google_column_number'] ?: $currentColumnNumber;
                if ($skip < $currentColumnNumber) {
                    $mapped[$skip] = $criterion['value'] ?? Google_Model::NULL_VALUE;
                } else {
                    while ($skip > $currentColumnNumber) {
                        $mapped[] = Google_Model::NULL_VALUE;
                        $currentColumnNumber++;
                    }
                }
                $mapped[] = $criterion['title'] ?? Google_Model::NULL_VALUE;
                $currentColumnNumber++;
            }
        }
        //$skip = $currentColumnNumber + 1;
        // while ($skip > $currentColumnNumber) {
        //     $mapped[] = Google_Model::NULL_VALUE;
        //     $currentColumnNumber++;
        // }

        // ВОЗРАЖЕНИЯ
        $skip = $data['objections_rate']['google_column_number'];
        while ($skip > $currentColumnNumber) {
            $mapped[] = Google_Model::NULL_VALUE;
            $currentColumnNumber++;
        }
        $mapped[] = $data['objections_rate']['value'] ?? Google_Model::NULL_VALUE;
        $currentColumnNumber++;

        $skip = $data['objections']['google_column_number'];
        while ($skip > $currentColumnNumber) {
            $mapped[] = Google_Model::NULL_VALUE;
            $currentColumnNumber++;
        }
        $mapped[] = $data['objections']['title'] ?? Google_Model::NULL_VALUE;
        $currentColumnNumber++;

        // CRM
        if (count($data['crm'] ?? [])) {
            foreach ($data['crm'] as $crm) {
                $skip = $crm['google_column_number'] ?: $currentColumnNumber;
                if ($skip < $currentColumnNumber) {
                    $mapped[$skip] = $crm['value'] ?? Google_Model::NULL_VALUE;
                } else {
                    while ($skip > $currentColumnNumber) {
                        $mapped[] = Google_Model::NULL_VALUE;
                        $currentColumnNumber++;
                    }
                    $mapped[] = $crm['value'] ?? Google_Model::NULL_VALUE;
                    $currentColumnNumber++;
                }
            }
        }

        $skip = $currentColumnNumber + 5;

        while ($skip > $currentColumnNumber) {
            $mapped[] = Google_Model::NULL_VALUE;
            $currentColumnNumber++;
        }

        // Комментарий
        $mapped[] = $data['comments'];

        // Доп. критерии
        if (count($data['additional_criteria'] ?? [])) {
            foreach ($data['additional_criteria'] as $datum) {
                $mapped[] = $datum['title'] ?? Google_Model::NULL_VALUE;
            }
        }
        // Сделка
        $mapped[] = $data['link_to_lead'];

        // Звонок
        //$mapped[] = $data['audio'];               // с токеном auth
        $mapped[] = $data['record_link_origin'];    // без токена

        $mapped[] = $data['assessor'] ?? Google_Model::NULL_VALUE;

        $mappedData[] = $mapped;

        return $mappedData;
    }

    public function getClient(): Google_Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getTabName(): ?string
    {
        return $this->tabName;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    private function filterParsedReports($reports): array
    {
        $result = array_filter($reports, function ($item) {
            if (!count($item) || !$item[0] || $item[0] === self::MONTH_ROW || $item[0] === self::DATES_ROW
                || str_contains($item[0], self::FG_DAILY)) {
                return false;
            }
            return true;
        });
        return array_values($result);
    }

    /**
     * @throws Exception
     */
    private function getColumnIndex($columnName, $columns, $project): int|string
    {
        $result = array_search($columnName, $columns);
        if (!$result) {
            throw new GoogleServiceException('В проекте ' . $project->name . ' id: ' . $project->id . ' колонка ' . $columnName . ' в листе "Оценки" не найдена');
        }
        return $result;
    }

    public function convertGoogleSheetsDate($daysCount): Carbon
    {
        // "Эпоха" для отсчета даты в Google Sheets начинается с 30 декабря 1899 года.
        $startDate = Carbon::parse('30.12.1899');
        return $startDate->addDays($daysCount);
    }
}
