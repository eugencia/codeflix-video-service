<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->removeDirectory();

        Model::reguard();

        factory(Video::class, 4)->make()
            ->each(function ($video) {

                Video::create(
                    array_merge(
                        $video->toArray(),
                        [
                            'video' => $this->makeFile('video', 'video/mp4'),
                            'banner' => $this->makeFile('banner', 'image/jpeg'),
                            'trailer' => $this->makeFile('trailer', 'video/mp4'),
                            'thumbnail' => $this->makeFile('thumbnail', 'image/jpeg'),
                        ],
                        $this->makeRelations()
                    )

                );
            });

        Model::unguard();
    }

    private function removeDirectory()
    {
        File::deleteDirectory(Storage::getDriver()->getAdapter()->getPathPrefix());
    }

    private function makeFile($name, $mimeType, $size = 0)
    {
        return UploadedFile::fake()->create($name, $size, $mimeType);
    }

    private function makeRelations()
    {
        $genresId = [];
        $categoriesId = [];

        $genres = Genre::inRandomOrder()->with('categories')->take(rand(1, 4))->get();

        foreach ($genres as $genre) {

            $genresId[] = $genre->id;

            foreach ($genre->categories as $category) {

                $categoriesId[] = $category->id;
            }
        }

        return [
            'genres' => $genresId,
            'categories' => array_unique($categoriesId),
        ];
    }
}
