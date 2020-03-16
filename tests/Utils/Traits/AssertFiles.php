<?php

namespace Tests\Utils\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait AssertFiles
{
    protected function assertFileInvalidation(string $field, string $extension, int $maxSise, string $rule, array $params = [])
    {
        $routes = [
            [
                'method' => "POST",
                'route' => $this->routeStore()
            ],
            [
                'method' => "PUT",
                'route' => $this->routeUpdate()
            ],
        ];

        foreach ($routes as $route) {

            $file = UploadedFile::fake()->create("$field.1$extension");
            $response = $this->json($route['method'], $route['route'], [$field => $file]);

            /**Invalida o tipo */
            $this->assertUnprocessableEntityField($response, [$field], $rule, $params);

            $file = UploadedFile::fake()->create("$field.$extension")->size($maxSise + 1);
            $response = $this->json($route['method'], $route['route'], [$field => $file]);

            /**Invalida ao tamanho do arquivo*/
            $this->assertUnprocessableEntityField($response, [$field], 'max.file', ['max' => $maxSise]);
        }
    }

    /**
     * Afirma a existência de arquivo(s)
     *
     * @param string $path
     * @param UploadedFile|UploadedFile[] $files
     * @return void
     */
    public function assertFilesExists(string $path, $files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                Storage::assertExists("{$path}/{$file->hashName()}");
            }

            return;
        }

        Storage::assertExists("{$path}/{$files->hashName()}");
    }

    /**
     * Afirma a não existência de arquivo(s)
     *
     * @param string $path
     * @param UploadedFile|UploadedFile[] $files
     * @return void
     */
    public function assertFilesNotExists(string $path, $files)
    {

        if (is_array($files)) {
            foreach ($files as $file) {
                Storage::assertMissing("{$path}/{$file->hashName()}");
            }

            return;
        }

        Storage::assertMissing("{$path}/{$files->hashName()}");
    }
}
