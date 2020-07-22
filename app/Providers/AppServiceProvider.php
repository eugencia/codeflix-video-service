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
use App\Observers\ModelObserver;
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
        Genre::observe(ModelObserver::class);
        Video::observe(ModelObserver::class);
        Category::observe(ModelObserver::class);
        CastMember::observe(ModelObserver::class);

        GenreVideo::observe(ModelObserver::class);
        CategoryGenre::observe(ModelObserver::class);
        CategoryVideo::observe(ModelObserver::class);
        CastMemberVideo::observe(ModelObserver::class);
    }
}
