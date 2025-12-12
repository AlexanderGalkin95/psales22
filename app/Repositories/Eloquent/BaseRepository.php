<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class BaseRepository implements RepositoryInterface
{
    protected Application $app;

    protected Model $model;


    public function __construct(?Model $model = null)
    {
        $this->app = app();
        $this->buildModel($model);
    }

    abstract public function model();


    /**
     * @throws Exception
     */
    protected function buildModel($model): void
    {
        if ($model instanceof Model) {
            $this->model = $model;
            return;
        }

        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} not instantiating Illuminate\\Database\\Eloquent\\Model");
        }
        $this->model = $model;
    }

    public function create(array $attributes): ?Model
    {
        $model = $this->model->create($attributes);
        return $model->fresh();
    }

    public function updateById(int $id, array $attributes): ?Model
    {
        $this->model = $this->findById($id);
        if ($this->model) {
            $this->model->update($attributes);
            return $this->model->fresh();
        }

        return null;
    }

    public function fillAndSave(array $attributes): Model
    {
        $this->model->fill($attributes)->save();
        return $this->model->fresh();
    }

    public function deleteById(int $id): bool
    {
        return false;
    }

    public function findById(int $id, ?array $columns = ['*'], array $relations = []): ?Model
    {
        if (empty($columns)) {
            $columns = ['*'];
        }
        return $this->model->with($this->parseWith($relations))->find($id, $columns);
    }

    public function findByAttributes(array $attributes, ?array $columns = ['*'], array $relations = []): ?Model
    {
        if (empty($columns)) {
            $columns = ['*'];
        }
        return $this->model->with($this->parseWith($relations))->where($attributes)->first($columns);
    }

    public function all(?array $columns = ['*'], array $relations = []): Collection
    {
        if (empty($columns)) {
            $columns = ['*'];
        }
        return $this->model->with($this->parseWith($relations))->get($columns);
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    protected function parseWith($relations): array
    {
        $results = [];
        foreach ($relations as $name => $constraints) {
            if (is_array($constraints)) {
                $query = function(Relation $q) use ($constraints) {
                    foreach ($constraints as $key => $value) {
                        $q->where("$key", '=', $value);
                    }
                };
                $results[$name] = $query;
            } else {
                $results[$name] = $constraints;
            }
        }
        return $results;
    }
}
