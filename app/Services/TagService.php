<?php

namespace App\Services;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagService
{
    public function __construct(
        private TagRepositoryInterface $tagRepository
    ) {}

    public function getUserTags(int $userId): LengthAwarePaginator
    {
        return $this->tagRepository->getUserTags($userId);
    }

    public function firstOrCreate(int $userId, string $name): Tag
    {
        return $this->tagRepository->firstOrCreate($userId, $name);
    }

    public function delete(int $id, int $userId): void
    {
        $tag = $this->tagRepository->findUserTagOrFail($id, $userId);
        $this->tagRepository->delete($tag);
    }
}
