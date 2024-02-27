<?php

namespace Mile6\LaravelEBMS\Models;

class Customer extends BaseModel {

    protected $table = 'ARCUST';

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
        'Address', 'Balance', 'DisplayInvoices', 'FULL_NAME', 'NonEmptyContacts',
        'PayOrders', 'Subtitle', 'Title',
    ];
}
