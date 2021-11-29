<?php
namespace plokko\TableHelper\Facades;

use Illuminate\Support\Facades\Facade;

class TableHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Plokko\TableHelper\TableHelper::class;
    }
}