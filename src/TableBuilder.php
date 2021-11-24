<?php

namespace plokko\TableHelper;

use DB;
use plokko\ResourceQuery\ResourceQuery;
use JsonSerializable;
use Illuminate\Contracts\Support\Responsable;
use plokko\ResourceQuery\ResourceQueryBuilder;

class TableBuilder implements TableBuilderInterface, \Illuminate\Contracts\Support\Arrayable, JsonSerializable, Responsable
{
    private
        /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\plokko\ResourceQuery\ResourceQuery */
        $query,
        /** @var TableColumnBuilder[] */
        $columns = [],
        /** @var array|null */
        $defaultSortBy = null,
        /** @var string|null */
        $baseLangFile = null,
        /** @var array|null */
        $select = null,
        /** @var string form action */
        $action = '',
        /** @var boolean */
        $autoSelect = true;

    /**
     * TableHelper constructor.
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|ResourceQuery|string $query
     */
    function __construct($query)
    {
        if (is_string($query)) {
            $query = DB::table($query);
        } /*elseif($query instanceof ResourceQuery){
            $this->autoSelect = false;
        }*/

        $this->query = $query;
    }


    /**
     * @param string $name
     * @return TableColumnBuilder
     */
    public function column($name): TableColumnBuilder
    {
        if (!isset($this->columns[$name])) {
            $this->columns[$name] = new TableColumnBuilder($this, $name);
        }
        return $this->columns[$name];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeColumn($name): TableBuilder
    {
        unset($this->columns[$name]);
        return $this;
    }

    public function setDefaultSortBy($attr)
    {
        $this->defaultSortBy = $attr;
        return $this;
    }

    public function setBaseLangFile($attr)
    {
        $this->baseLangFile = $attr;
        return $this;
    }

    public function selectFields(array $fields)
    {
        $this->select = $fields;
        return $this;
    }

    public function toResourceQuery($request = null): ResourceQuery
    {
        if ($this->query instanceof \plokko\ResourceQuery\ResourceQuery)
            return $this->query;
        $query = $this->query->clone();
        //SELECT FIELDS
        if ($this->select) {
            $query->select($this->select);
        } elseif ($this->autoSelect) {
            $query->select($this->getSelectedFields());
        }

        $rq = new ResourceQueryBuilder($query, $request);

        foreach ($this->columns as $name => $column) {
            $column->__apply($rq);
        }

        return $rq;
    }

    protected function getSelectedFields()
    {
        $fields = [];
        foreach ($this->columns AS $col) {
            /**@var TableColumnBuilder $col **/
            $f = $col->getSelectedField();
            if ($f)
                $fields[] = $f;
        }
        return $fields;
    }

    public function toArray()
    {
        return $this->toResourceQuery()->toArray();
    }

    public function formAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set auto field selection on or off on the form
     * @param boolean $enabled
     * @return $this
     */
    public function autoSelect($enabled)
    {
        $this->autoSelect = $enabled;
        return $this;
    }

    public function __toString()
    {
        return json_encode($this);
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

    public function toResponse($request)
    {
        return $this->toResourceQuery()->toResponse($request);
    }
    /**
     * Return headers array
     * @return array
     */
    public function getHeaders():array{
        $headers = [];
        foreach($this->columns AS $column){
            /**@var TableColumnBuilder $column **/
            if($column->visible){
                $headers[]= $column->toHeader();
            }
        }
        return $headers;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render(){
        return view('table-helper::table',[
            'headers' => $this->getHeaders(),
            'action' => $this->action,
            'method' => $this->method,
        ]);
    }
}