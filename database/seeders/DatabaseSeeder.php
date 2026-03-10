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
        $user = User::create([
            'name'     => 'Demo User',
            'email'    => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        $tags = Tag::factory()->count(5)->create(['user_id' => $user->id]);

        Note::factory()->count(10)->create(['user_id' => $user->id])->each(function ($note) use ($tags) {
            $note->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
        });
    }
}
