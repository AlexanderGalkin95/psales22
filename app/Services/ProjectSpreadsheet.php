<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use App\Models\HeatType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProjectSpreadsheet
{
    public static function mapHeaders($project): array
    {
        if (empty($project)) {
            return [];
        }

        $headers = [];
        $currentColumnNumber = 1;
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'Дата'];
        // Время
        //$headers['static_block'][$currentColumnNumber++] = ['text' => 'Время', 'type' => 'hidden'];
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'Время'];
        // Длина
        //$headers['static_block'][$currentColumnNumber++] = ['text' => 'Длина', 'type' => 'hidden'];
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'Длина'];
        // Менеджер
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'Менеджер'];
        // И/В
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'И/В', 'class' => 'text-vertical'];
        // ТИП
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'Тип'];
        // Теплота
        $headers['static_block'][$currentColumnNumber++] = ['text' => 'Теплота', 'class' => 'text-vertical'];

        // БЛОК КРИТЕРИЕВ
        static::mapCriteriaHeader($headers, $currentColumnNumber, $project);

        // БЛОК ВОЗРАЖЕНИЙ
        static::mapObjectionHeader($headers, $currentColumnNumber, $project);

        // БЛОК CRM
        static::mapCRMHeader($headers, $currentColumnNumber, $project);


        $headers['ending_block'][$currentColumnNumber++] = ['text' => 'Неделя', 'type' => 'hidden', 'class' => 'text-horizontal'];
        $headers['ending_block'][$currentColumnNumber++] = ['text' => 'День', 'type' => 'hidden', 'class' => 'text-horizontal'];
        $headers['ending_block'][$currentColumnNumber++] = ['text' => 'Месяц', 'type' => 'hidden', 'class' => 'text-horizontal'];

        // КОММЕНТАРИЙ
        $headers['ending_block'][$currentColumnNumber++] = ['text' => 'Комментарий'];
        // ДОП. ОЦЕНКА
        if ($project->additionalCriteria) {
            foreach ($project->additionalCriteria as $item) {
                $headers['ending_block'][$currentColumnNumber++] = ['text' => $item->name];
            }
        }

        // КНОПКИ
        $headers['ending_block'][$currentColumnNumber] = [
            'type' => 'buttons',
        ];

        return $headers;
    }

    private static function mapCriteriaHeader(&$headers, &$currentColumnNumber, $project)
    {
        $projectCriteria = $project->criteria->sortBy('google_column');
        foreach ($projectCriteria as $criterion) {
            $skip = CommonHelper::getNumberFromLetters($criterion->google_column);
            while ($skip > $currentColumnNumber) {
                $headers['criteria_block'][$currentColumnNumber] = ['text' => null];
                $currentColumnNumber++;
            }
            $headers['criteria_block'][$currentColumnNumber] = ['text' => $criterion->name, 'tooltip' => $criterion->legend];
            $currentColumnNumber++;
        }

        // Блок критериев - продолжение
        foreach ($projectCriteria as $criterion) {
            $headers['criteria_block'][$currentColumnNumber] = ['text' => $criterion->name, 'type' => 'hidden'];
            $currentColumnNumber++;
        }
        $headers['criteria_block'][$currentColumnNumber] = ['text' => 'FG', 'class' => 'text-horizontal'];
        $currentColumnNumber++;

        $skip = $project->objections->count()
            ? CommonHelper::getNumberFromLetters($project->objections->first()->google_column_rate)
            : $currentColumnNumber;
        while ($skip > $currentColumnNumber) {
            $headers['criteria_block'][$currentColumnNumber] = ['text' => null, 'type' => 'hidden'];
            $currentColumnNumber++;
        }
    }

    private static function mapObjectionHeader(&$headers, &$currentColumnNumber, $project)
    {
        $headers['objection_block'][$currentColumnNumber] = ['text' => 'Оценка возражения'];
        $currentColumnNumber++;

        $skip = $project->objections->count()
            ? CommonHelper::getNumberFromLetters($project->objections->first()->google_column)
            : $currentColumnNumber;
        while ($skip > $currentColumnNumber) {
            $headers['objection_block'][$currentColumnNumber] = ['text' => null];
            $currentColumnNumber++;
        }
        $headers['objection_block'][$currentColumnNumber] = ['text' => 'Возражения'];
        $currentColumnNumber++;
    }

    private static function mapCRMHeader(&$headers, &$currentColumnNumber, $project)
    {
        $projectCrm = $project->crm->sortBy('google_column');
        foreach ($projectCrm as $crm) {
            $skip = CommonHelper::getNumberFromLetters($crm->google_column);
            while ($skip > $currentColumnNumber) {
                $headers['crm_block'][$currentColumnNumber] = ['text' => null];
                $currentColumnNumber++;
            }
            $headers['crm_block'][$currentColumnNumber] = ['text' => $crm->name];
            $currentColumnNumber++;
        }

        // БЛОК CRM - ПРОДОЛЖЕНИЕ
        $headers['crm_block'][$currentColumnNumber++] = ['text' => 'CRM', 'class' => 'text-horizontal'];
    }

    public static function mapData(Collection $ratings, $project): Collection
    {
        // Получаем все типы теплоты и превращаем в массив вида [ 'system_name' => 'icon', 'system_name' => 'icon', ... ]
        $heatTypes = HeatType::all();
        return $ratings->transform(function ($rating) use ($project, $heatTypes) {
            $mapped = [];
            // TODO:: Временное решение пока не внесём нужные изменения в виджет
            $heat = $heatTypes->firstWhere('system_name', '=', $rating->heat)
                ?? $heatTypes->firstWhere('id', '=', $rating->heat);
            $mapped[] = ['text' => date('d.m.Y', strtotime($rating->created_date))];
            // Время
            //$mapped[] = ['text' => $rating->created_time, 'type' => 'hidden'];
            $mapped[] = ['text' => $rating->created_time];
            // Длина
            //$mapped[] = ['text' => date('i:s', strtotime($rating->duration)), 'type' => 'hidden'];
            $mapped[] = ['text' => date('i:s', strtotime($rating->duration))];
            // Менеджер
            $mapped[] = ['text' => $rating->manager];
            // И/В
            $mapped[] = ['text' => $rating->type, 'class' => 'text-center'];
            // ТИП
            $mapped[] = ['text' => $rating->call_type_value];
            // Теплота
            $mapped[] = ['text' =>  $heat->icon ?? null];

            $currentColumnNumber = 8;

            // CRITERIA
            static::mapCriteriaData($mapped, $currentColumnNumber, $rating, $project);
            $currentColumnNumber++;

            // ВОЗРАЖЕНИЯ
            static::mapObjectionData($mapped, $currentColumnNumber, $rating, $project);
            $currentColumnNumber++;

            // CRM
            static::mapCRMData($mapped, $currentColumnNumber, $rating, $project);

            // Номер недели
            $mapped[] = [
                'text' => CommonHelper::getWeek($rating->created_date),
                'type' => 'hidden',
                'style' => 'background-color:#d1d3e0cc;'
            ];
            // День месяца
            $mapped[] = [
                'text' => CommonHelper::getDay($rating->created_date),
                'type' => 'hidden',
                'style' => 'background-color:#d1d3e0cc;'
            ];
            // Месяц
            $mapped[] = [
                'text' => CommonHelper::getMonth($rating->created_date),
                'type' => 'hidden',
                'style' => 'background-color:#d1d3e0cc;'
            ];

            // Комментарий
            $mapped[] = ['text' => $rating->comments, 'style' => 'max-width:223.98px!important;'];

            // Дополнительный критерий
            if ($project->additionalCriteria) {
                if ($rating->additionalCriteria->isEmpty()) {
                    foreach ($project->additionalCriteria as $additionalCriterion) {
                        $mapped[] = ['text' => null];
                    }
                } else {
                    foreach ($rating->additionalCriteria as $additionalCriterion) {
                        $mapped[] = ['text' => $additionalCriterion->value ?? null];
                    }
                }
            }

            $mapped[] = [
                'type' => 'buttons',
                'buttons' => [
                    [
                        'text' => $rating->audio_link,
                        'type' => 'audio',
                        'class' => 'btn-outline-primary',
                        'icon' => 'fa-play',
                        'recordId' => $rating->call ? $rating->call->id : null,
                    ],
                    ['text' => static::parseLinkToLead($rating->link_to_lead, $project), 'type' => 'link', 'class' => 'btn-success', 'icon' => 'fa-handshake'],
                    ['text' => $rating->assessor->name, 'type' => 'tooltip', 'class' => 'btn-warning', 'icon' => 'fa-user']
                ]
            ];

            return $mapped;
        });
    }

    private static function mapCriteriaData(&$mapped, &$currentColumnNumber, $rating, $project)
    {
        $criteriaAVG = [];

        /* Группируем по ID полученные оценки критериев */
        $criteriaRatings = $rating->criteria->groupBy('criteria_id')->toArray();

        /* Сортируем по столбцу 'google_column', чтобы правильно упорядочить элементы */
        $projectCriteria = $project->criteria->sortBy('google_column');
        foreach ($projectCriteria as $criterion) {
            $skip = CommonHelper::getNumberFromLetters($criterion->google_column);
            while ($skip > $currentColumnNumber) {
                $mapped[] = ['text' => null];
                $currentColumnNumber++;
            }
            /* Из критериев достаем (если сущетствует) оценки заданного критерия и смотрим оценку критерия 'value' */
            $key = Arr::collapse(Arr::get($criteriaRatings, $criterion->id, []))['value'] ?? null;

            if (in_array($key, ['Да', '1'])) {
                $icon = 'mdi mdi-plus mdi-18px text-success';
            } elseif (in_array($key, ['ПолуДа', '0.5'])) {
                $icon = 'mdi mdi-plus-minus mdi-18px';
            } elseif (in_array($key, ['Нет', '0'])) {
                $icon = 'mdi mdi-minus mdi-18px text-danger';
            }
            else {
                $icon = '';
            }

            $mapped[] = [
                'text' => '',
                'type' => 'icon',
                'icon' => $icon
            ];

            /* Добавляем заливку к неоцениваемым критириям */
            $setting = $criterion->settings->firstWhere('call_type_id', '=', $rating->call_type_id);
            if (!$setting || empty($setting['points'])) {
                $mapped[$currentColumnNumber-1]['style'] = 'background-color: #b5b5c3';
            }
            $currentColumnNumber++;
        }

        $filteredRatingCriteria = $rating->settings->where('call_type_id', '=', $rating->call_type_id);
        foreach ($projectCriteria as $criterion) {
            /* Из критериев достаем (если сущетствует) оценки заданного критерия и смотрим оценку критерия 'value' */
            $key = Arr::collapse(Arr::get($criteriaRatings, $criterion->id, []))['value'] ?? null;
            $value = [
                'Да' => 1,
                '1' => 1,
                'ПолуДа' => 0.5,
                '0.5' => 0.5,
                'Нет' => 0,
                '0' => 0,
                null => null
            ][$key];
            $mapped[] = ['text' => $value, 'type' => 'hidden'];

            /* Получаем конкретную настройку (пересечение критерия и типа звонка) */
            $setting = $criterion->settings->firstWhere('call_type_id', '=', $rating->call_type_id);
            /* Вычисляем weight(веса) * points(оценку) */
            if ($setting && $setting['points'] && $value === null) {
                $filteredRatingCriteria = $filteredRatingCriteria->reject(function ($cr) use ($criterion) {
                    return $cr->criteria_id === $criterion->id;
                });
            } else {
                $criteriaAVG[] = $setting ? $setting['points'] * $value : null;
            }
            $currentColumnNumber++;
        }

        // FG (Расчетное поле) по $criteriaAVG[]
        $maxCriteriaAVG = array_reduce(
            $filteredRatingCriteria->toArray(),
            function ($carry, $item) {
                $carry += $item['points'];
                return $carry;
            }, 0);
        $fg = $maxCriteriaAVG
            ? round(100 * array_sum(array_filter($criteriaAVG)) / $maxCriteriaAVG, 0)
            : null;
        $fgBackground = $fg < 90 ? ($fg < 80 ? '#f64e60a6' : '#F6A205FF') : '#1bc5bd';
        $mapped[] = [
            'text' => $fg,
            'style' => "background-color: $fgBackground;text-align:right;"
        ];
    }

    private static function mapObjectionData(&$mapped, &$currentColumnNumber, $rating, $project)
    {
        $skip = $project->objections->count() ? CommonHelper::getNumberFromLetters($project->objections->first()->google_column_rate) : $currentColumnNumber;
        while ($skip > $currentColumnNumber) {
            $mapped[] = ['text' => null, 'type' => 'hidden'];
            $currentColumnNumber++;
        }
        $mapped[] = ['text' => $rating->objection ? $rating->objection->objection_rate : null];
        $currentColumnNumber++;

        $skip = $project->objections->count() ? CommonHelper::getNumberFromLetters($project->objections->first()->google_column) : $currentColumnNumber;
        while ($skip > $currentColumnNumber) {
            $mapped[] = ['text' => null];
            $currentColumnNumber++;
        }
        $mapped[] = ['text' => $rating->objection && $rating->objection->objection ? $rating->objection->objection->name : null];
    }

    private static function mapCRMData(&$mapped, &$currentColumnNumber, $rating, $project)
    {
        $crmAVG = [];
        $projectCrm = $project->crm->sortBy('google_column');
        $crmRatings = $rating->crm->groupBy('crm_field_id')->toArray();
        foreach ($projectCrm as $crm) {
            $skip = CommonHelper::getNumberFromLetters($crm->google_column);
            while ($skip > $currentColumnNumber) {
                $mapped[] = ['text' => null];
                $currentColumnNumber++;
            }
            $value = Arr::collapse(Arr::get($crmRatings, $crm->id, []))['value'] ?? null;
            $mapped[] = ['text' => ['Да' => '1', '1' => '1', 'Нет' => '0', '0' => '0', null => null][$value]];
            $crmAVG[] = ['Да' => 1, '1' => 1, 'Нет' => 0, '0' => 0, null => null][$value];
            $currentColumnNumber++;
        }

        // CRM (расчетное поле)
        $mapped[] = [
            'text' => count(array_filter($crmAVG))
                ? round(100 * array_sum(array_filter($crmAVG)) / count($crmAVG), 0)
                : null,
            'style' => 'background-color: #F6A205FF;text-align:right;'
        ];
    }

    private static function parseLinkToLead($link, $project)
    {
        $host = parse_url($link, PHP_URL_HOST);

        if (empty($host) && $project->integration) {
            $integrable = resolve($project->reference->type)->find($project->integration->integration_id);
            $link = "https://{$integrable->domain}$link";
        }
        return $link;
    }
}
