<?php

namespace App\Repositories;

use App\Models\Note;
use App\Repositories\Interfaces\NoteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoteRepository implements NoteRepositoryInterface
{
    public function create(array $data): Note
    {
        return Note::create($data);
    }

    public function update(Note $note, array $data): Note
    {
        $note->update($data);

        return $note->fresh();
    }

    public function delete(Note $note): void
    {
        $note->delete();
    }

    public function findOrFail(int $id): Note
    {
        return Note::with('tags')->findOrFail($id);
    }

    public function findTrashedOrFail(int $id, int $userId): Note
    {
        return Note::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function getUserNotes(int $userId): LengthAwarePaginator
    {
        return Note::where('user_id', $userId)
            ->with('tags')
            ->latest()
            ->paginate(10);
    }

    public function search(int $userId, string $query): LengthAwarePaginator
    {
        $lowerQuery = mb_strtolower($query);

        return Note::where('user_id', $userId)
            ->where(function ($q) use ($lowerQuery) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$lowerQuery}%"])
                  ->orWhereRaw('LOWER(content) LIKE ?', ["%{$lowerQuery}%"]);
            })
            ->with('tags')
            ->latest()
            ->paginate(10);
    }
}
