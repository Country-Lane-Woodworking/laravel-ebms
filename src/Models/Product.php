<?php

namespace Mile6\LaravelEBMS\Models;

class Product extends BaseModel {

    protected $table = 'INVENTRY';

    /**
     *
     */
    protected $fieldMapping = [
        'price' => 'COST',
        'max_inventory' => 'MAX_INVEN'
    ];

    /**
     * Fields that can only be read but never updated
     */
    protected $readOnly = [
        'AUTOID', 'Barcode', 'Classification', 'COUNT', 'CreateWithGuide', 'DATE',
        'DEF_UNIT_Reference', 'DEF_UNIT_ReferenceItems', 'DefaultSellingPrices',
        'FOLDERNAME', 'IsApplicable', 'IsDeleted', 'IsNewEntity', 'JOB_OUT_O',
        'JOB_OUT_S', 'MFG', 'M_IN_O', 'M_IN_S', 'M_OUT_O', 'M_OUT_S', 'MAX_INVEN',
        'MIN_INVEN', 'NET_ORDER', 'Order', 'ORDER_AMT', 'PUR_O', 'PUR_S',
        'SALES_O', 'SALES_S', 'Subtitle', 'T_AVAIL', 'T_ON_HAND', 'Title',
    ];

    public function scopeCategoryId($query, $categoryId, $or = false)
    {
        $query->{$or ? 'orWhere' : 'where'}('TREE_ID', str_repeat(' ', 5 - strlen((string) $categoryId)) . $categoryId);
    }

    public function scopeOrCategoryId($query, $categoryId)
    {
        $this->scopeCategoryId($query, $categoryId, true);
    }

    public function scopeActive($query)
    {
        $query->where('INACTIVE', false);
    }

    public function scopeExtra($query)
    {
        $query->where(function($query) {
            $query->where('TYPE', '!=', '')
                ->where('TYPE', '!=', 'Base');
        });
    }

    public function scopeBase($query)
    {
        $query->where(function($query) {
            $query->where('TYPE', '')
                ->orWhere('TYPE', 'Base');
        });
    }
}
