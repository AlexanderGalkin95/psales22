<?php

namespace App\Repositories\Contracts;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function create(array $attributes): ?Model;

    public function fillAndSave(array $attributes): Model;

    public function updateById(int $id, array $attributes): ?Model;

    public function deleteById(int $id): bool;

    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model;

    public function findByAttributes(array $attributes, array $columns = ['*'], array $relations = []): ?Model;

    public function all(array $columns = ['*'], array $relations = []): Collection;
}
