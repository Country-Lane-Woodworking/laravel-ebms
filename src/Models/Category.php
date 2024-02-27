<?php

namespace Mile6\LaravelEBMS\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Category extends BaseModel {

    protected $table = 'INVENTRE';

    /**
     *
     */
    protected $fieldMapping = [

    ];

    /**
     * Fields that can only be read but never updated
     */
    protected $readOnly = [];

    public function scopeParentId($query, $parentId)
    {
        $query->where('PARENT_ID', str_repeat(' ', 5 - strlen((string) $parentId)) . $parentId);
    }

    public function getChildCategories()
    {
        if(!$this->INVENTREs) {
            return static::where('PARENT_ID', $this->TREE_ID)->addExpand('INVENTREs', function ($query) {
                $query->addExpand('INVENTREs', function($query) {
                    $query->addExpand('INVENTREs');
                });
            })->get();
        }

        return new Collection(array_map(function($item) {
            return $this->newInstance($item);
        }, $this->INVENTREs ?? [] ));
    }
}
