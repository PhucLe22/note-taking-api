<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'notes_count' => $this->whenCounted('notes'),
            'tags_count'  => $this->whenCounted('tags'),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    public function with($request): array
    {
        return [
            'success' => true,
            'message' => 'Success',
        ];
    }
}
