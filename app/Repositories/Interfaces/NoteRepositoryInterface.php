<?php

namespace App\Repositories\Interfaces;

use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NoteRepositoryInterface
{
    public function create(array $data): Note;

    public function update(Note $note, array $data): Note;

    public function delete(Note $note): void;

    public function findOrFail(int $id): Note;

    public function findTrashedOrFail(int $id, int $userId): Note;

    public function getUserNotes(int $userId): LengthAwarePaginator;

    public function getTrashedNotes(int $userId): LengthAwarePaginator;

    public function search(int $userId, string $query): LengthAwarePaginator;

    public function forceDelete(Note $note): void;
}
