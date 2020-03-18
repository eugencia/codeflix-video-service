<?php

namespace Tests\Utils\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

trait AssertTests
{
    protected function executeTest($message = "Execute tests only env production")
    {
        if (!App::environment("production")) {
            $this->markTestSkipped($message);
        }
    }
}
