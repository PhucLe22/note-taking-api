<?php

namespace App\Providers;

use App\Repositories\Interfaces\NoteRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\NoteRepository;
use App\Repositories\TagRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NoteRepositoryInterface::class, NoteRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
