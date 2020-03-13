<?php

namespace Tests\Utils\Traits;

trait AssertTraits
{

    /**
     * Valida a utilização de traits
     *
     * @param string|array $traitClass
     * @param string $modelClass
     * @return void
     */
    protected function assertTraitsUse(
        $traitClass,
        $modelClass
    ) {

        $modelTraits = array_keys(class_uses($modelClass));

        $this->assertEquals($traitClass, $modelTraits);
    }
}
