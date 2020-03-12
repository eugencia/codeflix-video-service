<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenreHasCategories implements Rule
{
    /**
     *Array of categories ID
     *
     * @var array
     */
    private $genres;

    /**
     * Array of categories ID
     *
     * @var array
     */
    private $categories;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $categories)
    {
        $this->categories = array_unique($categories);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  array  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->genres = array_unique($value);

        if (!count($this->categories) || !count($this->genres))
            return false;

        $categoriesFounded = 0;

        foreach ($this->genres as $genre) {

            $rows = $this->getRows($genre);

            if($rows->count() === 0)
                return false;

            $categoriesFounded += count(array_unique($rows->pluck('category_id')->toArray()));

            #Nenhuma categoria foi encontrada para o gênero informado
            if (!$categoriesFounded) return false;
        }

        return $categoriesFounded === count($this->categories);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.genre_has_categories');
    }

    /**
     * Retorna as categorias relactionadas a um gênero
     *
     * @param int|string $genreId
     * @return Collection
     */
    protected function getRows($genreId): Collection
    {
        return DB::table('category_genre')
            ->where('genre_id', $genreId)
            ->whereIn('category_id', $this->categories)
            ->get();
    }
}
