<?php

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Genre::class, 5)->create()->each(function ($genre) {

            $qtd = rand(1, 3);

            $categoriesActive = Category::where('is_active', true)
                                    ->inRandomOrder()
                                    ->limit($qtd)
                                    ->get();

            $genre->categories()->sync($categoriesActive);
        });
    }
}
