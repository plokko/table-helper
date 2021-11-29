<?php
namespace plokko\TableHelper;

use plokko\ResourceQuery\ResourceQuery;

class TableHelper
{
    /**
     * Create a new TableHelper
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|ResourceQuery|string $query
     */
    public function table($query):TableBuilder{
        return new TableBuilder($query);
    }
}