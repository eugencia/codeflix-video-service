<?php

namespace App\Http\Requests;

use App\Enums\Classification;
use App\Enums\Size;
use App\Models\Video;
use App\Rules\GenreHasCategories;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:0',
            'classification' => [
                'required',
                'in:'.implode(',', Video::CLASSIFICATION)
            ],
            'release_at' => 'required|date|date_format:Y-m-d',
            'categories' =>  [
                'required',
                'array',
                'exists:categories,id,is_active,1,deleted_at,NULL',
            ],
            'genres' => [
                'required',
                'array',
                'exists:genres,id,is_active,1,deleted_at,NULL',
            ],
            'video' => 'file|mimetypes:video/mp4|max:12',//. Size::VIDEO,
            'banner' => 'image|max:12',//. Size::BANNER,
            'trailer' => 'file|mimetypes:video/mp4|max:12',//. Size::TRAILER,
            'thumbnail' => 'image|max:12'//. Size::THUMBNAIL
        ];
    }
}
