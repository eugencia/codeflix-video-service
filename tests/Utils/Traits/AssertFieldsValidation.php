<?php

namespace Tests\Utils\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;

trait AssertFieldsValidation
{
    protected function assertFieldsValidationInCreating(
        array $data,
        string $rule,
        array $params = []
    ) {
        $response = $this->json("POST", $this->routeStore(), $data);

        $this->assertUnprocessableEntityField($response, array_keys($data), $rule, $params);
    }

    protected function assertFieldsValidationInUpdating(
        array $data,
        string $rule,
        array $params = []
    ) {
        $response = $this->json("PUT", $this->routeUpdate(), $data);

        $this->assertUnprocessableEntityField($response, array_keys($data), $rule, $params);
    }

    protected function assertUnprocessableEntityField(
        TestResponse $response,
        array $attributes = [],
        string $rule,
        array $ruleParams = []
    ) {
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($attributes);

        foreach ($attributes as $attribute) {
            $field = str_replace('_', ' ', $attribute);
            $response->assertJsonFragment([
                Lang::get(
                    "validation.{$rule}",
                    ['attribute' => $field] + $ruleParams
                )
            ]);
        }
    }
}
