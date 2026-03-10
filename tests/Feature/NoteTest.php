<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------

    public function test_user_can_list_their_notes(): void
    {
        Note::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/notes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'title', 'content', 'tags', 'created_at']],
                'links',
                'meta',
            ]);
    }

    public function test_user_cannot_see_other_users_notes(): void
    {
        $other = User::factory()->create();
        Note::factory()->count(2)->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/notes');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    // -------------------------------------------------------
    // STORE
    // -------------------------------------------------------

    public function test_user_can_create_a_note(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/notes', [
            'title'   => 'My First Note',
            'content' => 'Some content here.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'My First Note')
            ->assertJsonStructure(['data' => ['id', 'title', 'content', 'tags', 'created_at']]);

        $this->assertDatabaseHas('notes', [
            'title'   => 'My First Note',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_note_with_tags(): void
    {
        $tags = Tag::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/notes', [
            'title' => 'Tagged Note',
            'tags'  => $tags->pluck('id')->toArray(),
        ]);

        $response->assertStatus(201);
        $this->assertCount(2, $response->json('data.tags'));
    }

    public function test_create_note_requires_title(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/notes', [
            'content' => 'No title here.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['title']]);
    }

    // -------------------------------------------------------
    // SHOW
    // -------------------------------------------------------

    public function test_user_can_view_their_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $note->id);
    }

    public function test_user_cannot_view_another_users_note(): void
    {
        $other = User::factory()->create();
        $note  = Note::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    public function test_user_can_update_their_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/notes/{$note->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_user_cannot_update_another_users_note(): void
    {
        $other = User::factory()->create();
        $note  = Note::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/notes/{$note->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    public function test_user_can_delete_their_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_delete_another_users_note(): void
    {
        $other = User::factory()->create();
        $note  = Note::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // RESTORE
    // -------------------------------------------------------

    public function test_user_can_restore_soft_deleted_note(): void
    {
        $note = Note::factory()->create(['user_id' => $this->user->id]);
        $note->delete();

        $response = $this->actingAs($this->user)->patchJson("/api/v1/notes/{$note->id}/restore");

        $response->assertStatus(200);
        $this->assertNotSoftDeleted('notes', ['id' => $note->id]);
    }

    // -------------------------------------------------------
    // SEARCH
    // -------------------------------------------------------

    public function test_user_can_search_notes(): void
    {
        Note::factory()->create(['user_id' => $this->user->id, 'title' => 'Laravel tips']);
        Note::factory()->create(['user_id' => $this->user->id, 'title' => 'Vue.js notes']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/notes/search?q=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_search_requires_query_param(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/notes/search');

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // AUTH GUARD
    // -------------------------------------------------------

    public function test_unauthenticated_user_cannot_access_notes(): void
    {
        $response = $this->getJson('/api/v1/notes');

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}
