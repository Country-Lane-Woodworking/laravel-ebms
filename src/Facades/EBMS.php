<?php

namespace Mile6\LaravelEBMS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mile6\LaravelEBMS\EBMS
 */
class EBMS extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ebms';
    }
}
