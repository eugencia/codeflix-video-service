<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenreRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255',
            'is_active' => 'boolean',
            'categories' => 'required|array|exists:categories,id,is_active,1,deleted_at,NULL',
        ];
    }
}
