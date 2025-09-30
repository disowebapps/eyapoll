<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface RepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Model;
    public function findOrFail(int $id): Model;
    public function findBy(array $criteria): Collection;
    public function findOneBy(array $criteria): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): bool;
    public function updateWhere(array $criteria, array $data): int;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15, array $criteria = []): LengthAwarePaginator;
    public function count(array $criteria = []): int;
    public function exists(array $criteria = []): bool;
    public function getQuery(): Builder;
    public function with(array $relations);
}