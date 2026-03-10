<?php

namespace App\Repositories\Interfaces;

use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TagRepositoryInterface
{
    public function getUserTags(int $userId): LengthAwarePaginator;

    public function firstOrCreate(int $userId, string $name): Tag;

    public function findUserTagOrFail(int $id, int $userId): Tag;

    public function delete(Tag $tag): void;
}
