<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
// use EloquentFilter\Filterable;
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

    protected function resource()
    {
        return GenreResource::class;
    }

    // public function index(Request $request)
    // {
    //     $query = Genre::query();

    //     /**
    //      * Aplica os filtros
    //      */
    //     if (in_array(Filterable::class, class_uses(Genre::class)))
    //         $query = $query->filter($request->all());

    //     $data = $request->has('all')
    //         ? $query->get()
    //         : $query->paginate($request->get('per_page'));

    //     return GenreResource::collection($data);
    // }

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

            return new GenreResource($genreCreated);
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

            return new GenreResource($genre);
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
