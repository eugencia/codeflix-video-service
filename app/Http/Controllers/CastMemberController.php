<?php

namespace App\Http\Controllers;

use App\Http\Requests\CastMemberRequest;
use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;

class CastMemberController extends Controller
{
    protected function model()
    {
        return CastMember::class;
    }

    protected function request()
    {
        return CastMemberRequest::class;
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }
}
