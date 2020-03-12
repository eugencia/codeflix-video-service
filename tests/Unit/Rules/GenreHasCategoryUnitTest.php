<?php

namespace Tests\Unit\Rules;

use App\Rules\GenreHasCategories;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GenreHasCategoryUnitTest extends TestCase
{
    /**
     * Valida o retorno de um array de categorias com valores únicos
     *
     * @return void
     */
    public function testUniqueCategoriesFields()
    {
        $rule = new GenreHasCategories([1, 1, 2, 2]);

        $reflectionClass = new ReflectionClass(GenreHasCategories::class);
        $reflectionProperty = $reflectionClass->getProperty('categories');
        $reflectionProperty->setAccessible(true);

        $categories = $reflectionProperty->getValue($rule);

        $this->assertEqualsCanonicalizing([1, 2], $categories);
    }

    /**
     * Valida o retorno de um array de gêneros com valores únicos
     *
     * @return void
     */
    public function testUniqueGenresFields()
    {
        $rule = $this->createRuleMock([]);
        $rule->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([]));

        $rule->passes('', [1, 1, 2, 2]);

        $reflectionClass = new ReflectionClass(GenreHasCategories::class);
        $reflectionProperty = $reflectionClass->getProperty('genres');
        $reflectionProperty->setAccessible(true);

        $genres = $reflectionProperty->getValue($rule);

        $this->assertEqualsCanonicalizing([1, 2], $genres);
    }

    /**
     * Valida o retorno de quando o array de Gêneros está vazio
     *
     * @return void
     */
    public function testAssertReturnsFalseWhenGenresIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));
    }

    /**
     * Valida o retorno falso de quando  o array de Categorias está vazia
     *
     * @return void
     */
    public function testAssertReturnsFalseWhenCategoriesIsArrayEmpty()
    {
        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    /**
     * Valida o retorno falso quando getRows retorna vazio
     *
     * @return void
     */
    public function testAssertReturnsFalseWhenGetRowsReturnEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $rule->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect());

        $this->assertFalse($rule->passes('', [1]));
    }

    /**
     * Valida se a entrada de categorias informadas é a mesma de
     * categorias encontradas no BD
     *
     * @return void
     */
    public function testAssertReturnsFalseWhenGenreDoesNotHaveCategories()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect(['categories' => 1]));

        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesWhenCategoriesReportedAreTheSameAsThoseFoundInGetRows()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2],
                ['category_id' => 1],
                ['category_id' => 2]
            ]));

        $this->assertTrue($rule->passes('', [1]));
    }

    /**
     * Cria um mock da classe de GenreHasCategoryRule
     *
     * @param array $categories
     * @return MockInterface
     */
    protected function createRuleMock(array $categories): MockInterface
    {
        return Mockery::mock(GenreHasCategories::class, [$categories])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
