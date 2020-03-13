<?php

namespace Tests\Stubs\Models;

use App\Traits\Uploader;
use Illuminate\Database\Eloquent\Model;

class VideoStub extends Model
{
    use Uploader;

    public static $fileFields = [
        'file',
        'image',
    ];

    protected function path()
    {
        return '1';
    }
}
