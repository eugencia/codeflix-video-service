<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected abstract function model();

    protected abstract function request();

    protected abstract function resource();

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resource = $this->resource();

        return $this->resource()::collection($this->model()::paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show($object)
    {
        $object = $this->getModelBy($object);

        $resource = $this->resource();

        return new $resource($object);
    }

    public function store(Request $request)
    {
        $objectCreated = $this->model()::create($this->getDataValidated($request));

        $objectCreated->refresh();

        $resource = $this->resource();

        return new $resource($objectCreated);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $value)
    {
        $object = $this->getModelBy($value);

        $data = $this->getDataValidated($request);

        $object->update($data);

        $object->refresh();

        $resource = $this->resource();

        return new $resource($object);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($value)
    {
        $this->getModelBy($value)->delete();

        return response()->noContent();
    }

    protected function getModelBy($value)
    {
        $model = $this->model();

        $keyName = (new $model)->getKeyName();

        return $this->model()::where($keyName, $value)
                    ->firstOrFail();
    }

    private function getDataValidated(Request $request)
    {
        $class = $this->request();

        $formRequest = new $class;

        return $this->validate($request, $formRequest->rules());
    }
}
