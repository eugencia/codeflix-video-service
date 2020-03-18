<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

abstract class DefaultFilter extends ModelFilter
{
    /**
     * Colunas a serem ordenáveis
     *
     * @var array
     */
    protected $sortable = [];

    public function setUp()
    {
        $this->blacklistMethod('isSortable');

        if (!$this->input('sort')) {
            $this->oldest();
        }
    }

    public function sort(string $column)
    {
        if ($this->isSortable($column)) {
            // dd($column);
            $direction = strtolower($this->input('dir')) === 'asc' ? 'asc' : 'desc';

            $this->orderBy($column, $direction);
        }
    }

    /**
     * Verifica se a coluna está entre as ordenáveis
     *
     * @param string $column
     * @return boolean
     */
    protected function isSortable(string $column)
    {
        return in_array($column, $this->sortable);
    }
}
