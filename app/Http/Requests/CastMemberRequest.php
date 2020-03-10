<?php

namespace App\Http\Requests;

use App\Enums\CastMemberType;
use App\Models\CastMember;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class CastMemberRequest extends FormRequest
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
            'name' => 'required|max:255',
            'role' => 'required|in:' . implode(',', [
                CastMember::ACTOR,
                CastMember::ACTRIZ,
                CastMember::DIRECTOR
            ])
        ];
    }
}
