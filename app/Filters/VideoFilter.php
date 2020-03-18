<?php 

namespace App\Filters;

class VideoFilter extends DefaultFilter
{
    protected $sortable = [
        'title',
        'release_at',
        'classification',
        'updated_at',
    ];
    
    public function search($search)
    {
        $this->where('title', 'LIKE', "%{$search}%")->get();
    }
}
