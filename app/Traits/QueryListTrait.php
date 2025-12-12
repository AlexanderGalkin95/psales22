<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

trait QueryListTrait
{
    protected function applyFilterClosure($request, $mapping): \Closure
    {
        return function ($q) use ($request, $mapping) {
            foreach (explode(' $and$ ', $request->input('$filter')) as $v) {
                list($field, $op, $value) = explode(' ', $v, 3);
                $val_arr = [];
                if (preg_match("/^'(.*)'$/i", $value)) {
                    $isLiteral = true;
                    $value = '%' . substr($value, 1, -1) . '%';
                } else {
                    $isLiteral = false;
                }
                if (!$isLiteral) {
                    $val_arr = explode('|', $value);
                }
                $isRelation = false;
                if (array_key_exists($field, $mapping)) {
                    $dbField = $mapping[$field];
                } elseif (array_key_exists( "relation:$field", $mapping)) {
                    $isRelation = true;
                    $dbField = $mapping["relation:$field"];
                } else {
                    continue;
                }
                switch ($op) {
                    case 'eq':
                        if ($isLiteral) {
                            if ($isRelation) {
                                $q->whereHas($field, function ($query) use ($value, $dbField) {
                                    $query->where($dbField, 'ilike', $value);
                                });
                            } else {
                                $q->where($dbField, 'ilike', $value);
                            }
                        } else {
                            if ($isRelation) {
                                $q->whereHas($field, function ($query) use ($value, $dbField) {
                                    $query->where($dbField, '=', $value);
                                });
                            } else {
                                $q->where($dbField, '=', $value);
                            }
                        }
                        break;
                    case '=':
                        if ($value === 'null') {
                            if ($isRelation) {
                                $q->whereHas($field, function ($query) use ($dbField) {
                                    $query->whereNull($dbField);
                                });
                            } else {
                                $q->whereNull($dbField);
                            }
                        } else {
                            if (count($val_arr)) {
                                if ($isRelation) {
                                    $q->whereHas($field, function ($query) use ($val_arr, $dbField) {
                                        $query->whereIn($dbField, $val_arr);
                                    });
                                } else {
                                    $q->whereIn($dbField, $val_arr);
                                }
                            } else {
                                if ($isRelation) {
                                    $q->whereHas($field, function ($query) use ($val_arr, $dbField) {
                                        $query->whereIn($dbField, '=', $val_arr);
                                    });
                                } else {
                                    $q->where($dbField, '=', $value);
                                }
                            }
                        }
                        break;
                    case 'btw':
                        $dates = explode('~', $value);
                        $q->whereBetween($dbField, $dates);
                        break;
                    default:
                        break;
                }
            }
            return $q;
        };
    }

    protected function applyOrderByClosure($request, $mapping): \Closure
    {
        return function ($q) use ($request, $mapping) {
            foreach (explode(',', $request->input('$orderBy')) as $v) {
                list($field, $direction) = explode(' ', $v);
                if (array_key_exists($field, $mapping)) {
                    $dbField = $mapping[$field];
                } else {
                    continue;
                }
                $q->orderBy($dbField, $direction);
            }
            return $q;
        };
    }

    protected function getCount($query): int
    {
        return (int)DB::selectOne("select count (*) as total from (" . $query->toSql() . ") as t", $query->getBindings())->total;
    }

    protected function respProjectNotFound(): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'code'    => 3,
            'message' => 'Проект не найден'
        ], 404);
    }

}