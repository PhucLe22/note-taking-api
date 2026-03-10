<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
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

    public function test_user_can_list_their_tags(): void
    {
        Tag::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tags');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_user_cannot_see_other_users_tags(): void
    {
        $other = User::factory()->create();
        Tag::factory()->count(2)->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tags');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    // -------------------------------------------------------
    // STORE
    // -------------------------------------------------------

    public function test_user_can_create_a_tag(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/tags', [
            'name' => 'work',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'work');

        $this->assertDatabaseHas('tags', [
            'name'    => 'work',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_tag_requires_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/tags', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_duplicate_tag_returns_existing(): void
    {
        Tag::factory()->create(['user_id' => $this->user->id, 'name' => 'work']);

        $response = $this->actingAs($this->user)->postJson('/api/v1/tags', [
            'name' => 'work',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('tags', 1);
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    public function test_user_can_delete_their_tag(): void
    {
        $tag = Tag::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_user_cannot_delete_another_users_tag(): void
    {
        $other = User::factory()->create();
        $tag   = Tag::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(404);
    }

    // -------------------------------------------------------
    // AUTH GUARD
    // -------------------------------------------------------

    public function test_unauthenticated_user_cannot_access_tags(): void
    {
        $response = $this->getJson('/api/v1/tags');

        $response->assertStatus(401);
    }
}
