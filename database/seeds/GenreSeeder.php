<?php

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Genre::class, 25)
            ->create()
            ->each(function ($genre) {

                $categoriesActive = Category::where('is_active', true)
                    ->inRandomOrder()
                    ->limit(rand(1, 3))
                    ->get();

                $genre->categories()->sync($categoriesActive);
            });
    }
}
