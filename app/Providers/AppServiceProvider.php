<?php

namespace App\Providers;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Pivot\CastMemberVideo;
use App\Models\Pivot\CategoryGenre;
use App\Models\Pivot\CategoryVideo;
use App\Models\Pivot\GenreVideo;
use App\Models\Video;
use App\Observers\Observer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Genre::observe(Observer::class);
        Video::observe(Observer::class);
        Category::observe(Observer::class);
        CastMember::observe(Observer::class);

        GenreVideo::observe(Observer::class);
        CategoryGenre::observe(Observer::class);
        CategoryVideo::observe(Observer::class);
        CastMemberVideo::observe(Observer::class);
    }
}
