<?php

namespace Tests\Utils\Traits;

trait AssertTheUseOfTraits
{

    /**
     * Undocumented function
     *
     * @param string|array $traitClass
     * @param string $modelClass
     * @return void
     */
    protected function assertTheUseOfTraits(
        $traitClass,
        $modelClass
    ) {

        $modelTraits = array_keys(class_uses($modelClass));

        $this->assertEquals($traitClass, $modelTraits);
    }
}
