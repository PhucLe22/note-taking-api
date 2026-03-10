<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagRepository implements TagRepositoryInterface
{
    public function getUserTags(int $userId): LengthAwarePaginator
    {
        return Tag::where('user_id', $userId)
            ->orderBy('name')
            ->paginate(10);
    }

    public function firstOrCreate(int $userId, string $name): Tag
    {
        return Tag::firstOrCreate([
            'user_id' => $userId,
            'name'    => $name,
        ]);
    }

    public function findUserTagOrFail(int $id, int $userId): Tag
    {
        return Tag::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}
