<?php

namespace Tests\Utils\Traits;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\TestResponse;

trait AssertFields
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

    /**
     * Define a classe do model em teste
     *
     * @return string
     */
    protected abstract function model();

    /**
     * Faz a verificação dos attributes ao criar uma nova instância
     *
     * @param array $data
     * @param array $attributesInTestOnDatabase
     * @param array $attributesInTestOnJsonResponse
     * @return TestResponse
     */
    protected function assertFieldsOnCreate(
        array $data,
        array $attributesInTestOnDatabase,
        array $attributesInTestOnJsonResponse = []
    ): TestResponse {
        $response = $this->makeResponse(
            "POST",
            $this->routeStore(),
            $data,
            Response::HTTP_CREATED
        );

        $this->assertOnDatabase($response, $attributesInTestOnDatabase);

        if(count($attributesInTestOnJsonResponse))
            $this->assertOnResponse($response, $attributesInTestOnJsonResponse);

        return $response;
    }

    /**
     * Faz a verificação dos attributes ao atualizar uma nova instância
     *
     * @param array $data
     * @param array $attributesInTestOnDatabase
     * @param array $attributesInTestOnJsonResponse
     * @return TestResponse
     */
    protected function assertFieldsOnUpdate(
        array $data,
        array $attributesInTestOnDatabase,
        array $attributesInTestOnJsonResponse = []
    ): TestResponse {

        $response = $this->makeResponse(
            "PUT",
            $this->routeUpdate(),
            $data,
            Response::HTTP_OK
        );

        $this->assertOnDatabase($response, $attributesInTestOnDatabase);

        if(count($attributesInTestOnJsonResponse))
            $this->assertOnResponse($response, $attributesInTestOnJsonResponse);

        return $response;
    }

    /**
     * Faz a requisição http ao servidor
     *
     * @param string $method
     * @param string $route
     * @param array $data
     * @param integer $statusCode
     * @return TestResponse
     */
    private function makeResponse(
        string $method,
        string $route,
        array $data,
        int $statusCode = Response::HTTP_OK
    ): TestResponse {
        $response = $this->json($method, $route, $data);

        if ($response->status() !== $statusCode)
            throw new Exception(
                "Response status code must be {$statusCode},
                but given {$response->status()}.\n
                {$response->content()}"
            );

        return $response;
    }

    /**
     * Junção das funções de asserções no banco de dados e json response
     *
     * @param TestResponse $response
     * @param array $attributes
     * @return void
     */
    private function makeAssertions(
        TestResponse $response,
        array $attributesInTestOnDatabase,
        array $attributesInTestOnJsonResponse
    ): void {
        $this->assertOnDatabase($response, $attributesInTestOnDatabase);

        $this->assertOnResponse($response, $attributesInTestOnJsonResponse);
    }

    /**
     * Asserções a serem verificadas no bando de dados
     *
     * @param TestResponse $response
     * @param array $attributes
     * @return void
     */
    private function assertOnDatabase(TestResponse $response, array $attributes = []): void
    {
        $attributes += ['id' => $response->json('id')];

        $this->assertDatabaseHas($this->getModel()->getTable(), $attributes);
    }

    /**
     * Asserções a serem verificas no Json response
     *
     * @param TestResponse $response
     * @param array $attributes
     * @return void
     */
    private function assertOnResponse(TestResponse $response, array $attributes): void
    {
        $attributes += ['id' => $response->json('id')];

        $response->assertJsonFragment($attributes);
    }

    /**
     * Retorna uma instancia do model
     *
     * @return Model
     */
    private function getModel()
    {
        $model = $this->model();

        return (new $model);
    }
}
