<?php

namespace App\Services;

use App\Models\Note;
use App\Repositories\Interfaces\NoteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoteService
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository
    ) {}

    public function getUserNotes(int $userId): LengthAwarePaginator
    {
        return $this->noteRepository->getUserNotes($userId);
    }

    public function getTrashedNotes(int $userId): LengthAwarePaginator
    {
        return $this->noteRepository->getTrashedNotes($userId);
    }

    public function findOrFail(int $id): Note
    {
        return $this->noteRepository->findOrFail($id);
    }

    public function create(array $data, int $userId): Note
    {
        $data['user_id'] = $userId;

        $note = $this->noteRepository->create($data);

        if (!empty($data['tags'])) {
            $note->tags()->sync($data['tags']);
        }

        return $note->load('tags');
    }

    public function update(Note $note, array $data): Note
    {
        $note = $this->noteRepository->update($note, $data);

        if (array_key_exists('tags', $data)) {
            $note->tags()->sync($data['tags']);
        }

        return $note->load('tags');
    }

    public function delete(Note $note): void
    {
        $this->noteRepository->delete($note);
    }

    public function restore(int $id, int $userId): Note
    {
        $note = $this->noteRepository->findTrashedOrFail($id, $userId);

        $note->restore();

        return $note->load('tags');
    }

    public function search(int $userId, string $query): LengthAwarePaginator
    {
        return $this->noteRepository->search($userId, $query);
    }

    public function forceDelete(int $id, int $userId): void
    {
        $note = $this->noteRepository->findTrashedOrFail($id, $userId);
        $this->noteRepository->forceDelete($note);
    }
}
