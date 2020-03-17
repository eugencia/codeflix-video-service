<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenreHasCategories;
use Illuminate\Http\Request;

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

    protected function resource()
    {
        return VideoResource::class;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return VideoResource
     */
    public function store(Request $request)
    {
        $this->addRule($request);

        $data = $this->validate($request, $this->getRules());

        $video = Video::create($data);
        $video->refresh();

        return new VideoResource($video);
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
        $this->addRule($request);

        $data = $this->validate($request, $this->getRules());

        $video = $this->getModelBy($video);
        $video->update($data);
        $video->refresh();

        return new VideoResource($video);
    }

    protected function getRules()
    {
        $videoRequest = $this->request();

        $videoRequest = new $videoRequest;

        return $videoRequest->rules();
    }

    protected function addRule(Request $request)
    {
        $categories = is_array($request->get('categories')) ? $request->get('categories') : [];

        $this->getRules()['genres'][] = new GenreHasCategories($categories);
    }
}
