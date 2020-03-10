<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

/**
 * Manage files in storage
 */
trait ExecuteTest {

    protected function executeTest($message = "Execute tests only env production") {

        if(! $this->isEnviromentProduction()) {
            $this->markTestSkipped($message);
        }

    }

    protected function isEnviromentProduction()
    {
        if(App::environment("production")) {
            return true;
        }

        return false;
    }
}