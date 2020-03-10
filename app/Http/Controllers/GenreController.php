<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenreController extends Controller
{
    protected function model()
    {
        return Genre::class;
    }

    protected function request()
    {
        return GenreRequest::class;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\GenreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $genreRequest = new GenreRequest;

        $data = $this->validate($request, $genreRequest->rules());

        try {
            DB::beginTransaction();

            $genreCreated = Genre::create($data);

            $this->syncRelations($genreCreated, $data);

            $genreCreated->refresh();

            DB::commit();

            return $genreCreated;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\GenreRequest  $request
     * @param  mixed  $genre
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $genre)
    {
        $genre = $this->getModelBy($genre);
        $genreRequest = new GenreRequest;

        $data = $this->validate($request, $genreRequest->rules());

        try {
            DB::beginTransaction();

            $genre->update($data);

            $this->syncRelations($genre, $data);

            $genre->refresh();

            DB::commit();

            return $genre;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    protected function syncRelations(Genre $genre, array $data = []): void
    {
        $genre->categories()->sync($data['categories']);
    }
}
