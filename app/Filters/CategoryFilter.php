<?php 

namespace App\Filters;

class CategoryFilter extends DefaultFilter
{
    protected $sortable = [
        'name',
        'is_active',
        'created_at'
    ];
    
    public function search($search)
    {
        $this->where('name', 'LIKE', "%{$search}%")->get();
    }
}
