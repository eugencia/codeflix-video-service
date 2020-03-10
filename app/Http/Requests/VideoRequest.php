<?php

namespace App\Http\Requests;

use App\Enums\Classification;
use App\Enums\Size;
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
            'duration' => 'required|numeric|min:0',
            'classification' => [
                'required',
                new EnumValue(Classification::class, false)
            ],
            'release_at' => 'required|date',
            'categories' =>  [
                'required',
                'array',
                'exists:categories,id,is_active,1,deleted_at,NULL',
            ],
            'genres' => [
                'required',
                'array',
                'exists:genres,id,is_active,1,deleted_at,NULL',
                // new GenreHasCategories($this->categories)
            ],
            // 'video_file' => 'file|mimetypes:video/mp4|max:'. Size::VIDEO,
            // 'banner_file' => 'image|max:'. Size::BANNER,
            // 'trailer_file' => 'file|mimetypes:video/mp4|max:'. Size::TRAILER,
            // 'thumbnail_file' => 'image|max:'. Size::THUMBNAIL
        ];
    }
}
