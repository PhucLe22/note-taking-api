<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin demo account
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $adminTags = Tag::factory()->count(3)->create(['user_id' => $admin->id]);

        Note::factory()->count(5)->create(['user_id' => $admin->id])->each(function ($note) use ($adminTags) {
            $note->tags()->attach($adminTags->random(rand(1, 2))->pluck('id'));
        });

        // Regular user demo account
        $user = User::create([
            'name'     => 'Demo User',
            'email'    => 'demo@example.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);

        $tags = Tag::factory()->count(5)->create(['user_id' => $user->id]);

        Note::factory()->count(10)->create(['user_id' => $user->id])->each(function ($note) use ($tags) {
            $note->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
        });
    }
}
