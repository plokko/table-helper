<?php

namespace plokko\TableHelper;

use DB;
use plokko\ResourceQuery\ResourceQuery;
use JsonSerializable;
use Illuminate\Contracts\Support\Responsable;
use plokko\ResourceQuery\ResourceQueryBuilder;
use Illuminate\Support\HtmlString;

class TableBuilder implements TableBuilderInterface, \Illuminate\Contracts\Support\Arrayable, JsonSerializable, Responsable
{
    private
        /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\plokko\ResourceQuery\ResourceQuery */
        $query,
        /** @var TableColumnBuilder[] */
        $columns = [],
        /** @var TableFilterCondition[] */
        $filters = [],
        /** @var array|null */
        $defaultSortBy = null,
        /** @var string|null */
        $baseLangFile = null,
        /** @var array|null */
        $select = null,
        /** @var string form action */
        $action = '',
        /** @var boolean */
        $autoSelect = true,
        /**@var string|null */
        $useResource = null,
        /**@var int Page size */
        $pageSize=null;

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
        if ($this->query instanceof \plokko\ResourceQuery\ResourceQuery) {
            $query = $this->query->clone();
        }else{
            $q = $this->query->clone();
            //SELECT FIELDS
            if ($this->select) {
                $q->select($this->select);
            } elseif ($this->autoSelect) {
                $q->select($this->getSelectedFields());
            }

            $query = new ResourceQueryBuilder($q, $request);
        }
        foreach ($this->columns as $name => $column) {
            $column->__apply($query);
        }
        foreach ($this->filters AS $filter){
            /** @var TableFilterCondition $filter */
            $filter->_applyToResourceQuery($query);
        }


        if($this->defaultSortBy)
            $query->setDefaultOrder($this->defaultSortBy);

        if($this->useResource)
            $query->useResource($this->useResource);

        if($this->pageSize!==null){
            $query->setPagination($this->pageSize);
        }

        return $query;
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
        $labels = null;
        if($this->baseLangFile){
            $labels = trans($this->baseLangFile);
            if(!is_array($labels))
                $labels = null;
        }
        $headers = [];
        foreach($this->columns AS $column){
            /**@var TableColumnBuilder $column **/
            if($column->visible){
                $opt = [];
                if(!empty($labels[$column->name]))
                    $opt['label'] = $labels[$column->name];//From global trans
                $headers[]= $column->toHeader($opt);
            }
        }
        return $headers;
    }

    /**
     * @return Htmlable|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render(){
        return view(config('table-helper.base-view'),[
            'table' => $this,
        ]);
    }

    public function renderAttr(){
        return new HtmlString(' :headers="'.e(json_encode($this->getHeaders())).'" action="'.e($this->action).'" ');
    }
    public function renderBody(){
        $content = '';
        foreach($this->columns AS $column){
            /**@var TableColumnBuilder $column **/
            $r = $column->_render();
            if($r)
                $content.=$r;
        }
        return new HtmlString($content);
    }

    /**
     * @param string $name
     * @param $condition
     * @param $field
     * @return TableFilterCondition
     */
    function addFilter($name, $condition = null, $field = null): TableFilterCondition
    {
        if(!isset($this->filters[$name])){
            $this->filters[$name] = new TableFilterCondition($this,$name,$field);
        }
        if ($condition)
            $this->filters[$name]->condition($condition);
        if ($field)
            $this->filters[$name]->field($field);

        return $this->filters[$name];
    }

    /**
     * Remove a filter by name
     * @param string $name Filter name
     * @return $this
     */
    function removeFilter($name):TableBuilder
    {
        unset($this->filters[$name]);
        return $this;
    }

    /**
     * Remove a filter by name
     * @param string $name Filter name
     * @return $this
     */
    function useResource($name)
    {
        $this->useResource = $name;
        return $this;
    }

    /**
     * Set page size
     * @param int|null|array $pagination
     * @return $this
     */
    function setPageSize($size){
        $this->pageSize = $size;
        return $this;
    }
}
