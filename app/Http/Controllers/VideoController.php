<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    protected function model()
    {
        return Video::class;
    }

    protected function request()
    {
        return VideoRequest::class;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return VideoResource
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $formRequest = new VideoRequest;

            $data = $this->validate($request, $formRequest->rules());

            $video = Video::create($data);

            $this->syncRelations($video, $data);

            $video->refresh();

            DB::commit();

            return $video;
        } catch (\Throwable $th) {
            if (isset($video)) {
                // $video->remove($fileFields);
            }
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $video
     * @return VideoResource
     */
    public function update(Request $request, $video)
    {
        try {
            DB::beginTransaction();

            $formRequest = new VideoRequest;

            $data = $this->validate($request, $formRequest->rules());

            $video = $this->getModelBy($video);

            $video->update($data);

            $this->syncRelations($video, $data);

            $video->refresh();

            DB::commit();

            return $video;
        } catch (\Throwable $th) {
            if (isset($video)) {
                // $video->remove($fileFields);
            }
            DB::rollBack();
            throw $th;
        }
    }

    protected function syncRelations(Video $video, array $relations = []): void
    {
        $video->categories()->sync($relations['categories']);

        $video->genres()->sync($relations['genres']);
    }
}
