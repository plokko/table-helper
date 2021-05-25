<?php

namespace plokko\TableHelper;


use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use plokko\ResourceQuery\ResourceQuery;
use plokko\ResourceQuery\ResourceQueryBuilder;

class TableHelper implements Responsable,\JsonSerializable
{
    private
        $query,
        $action='',
        /**
         * @var TableColumn[]
         */
        $columns = [],
        $hiddenFields = null,
        $autoSelect=false;

    /**
     * TableHelper constructor.
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|ResourceQuery|string $query
     */
    function __construct($query){
        if(is_string($query)){
            $query = \DB::table($query)->newQuery();
        }
        $this->query = $query;
    }


    /**
     * @param string $name
     * @return $this
     */
    function removeColumn($name){
        unset($this->columns[$name]);
        return $this;
    }
    public function offsetExists($offset)
    {
        return isset($this->colums[$offset]);
    }

    public function offsetGet($offset)
    {
        return empty($this->columns[$offset]) ?
            $this->column($offset)
            : $this->columns[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadFunctionCallException("Columns cannot be set");
    }

    public function offsetUnset($offset)
    {
        unset($this->columns[$offset]);
    }

    /**
     * @param string $action
     * @return $this
     */
    public function action($action='')
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param bool $autoselect
     * @return $this
     */
    public function autoSelect($autoselect=true){
        $this->autoSelect = $autoselect;
        return $this;
    }

    /**
     * @param string $name
     * @return TableColumn
     */
    function column($name,$field=null,$label=null):TableColumn{
        if(!isset($this->colums[$name])){
            $this->columns[$name] = new TableColumn($this,$name);
        }
        return $this->columns[$name];
    }

    /**
     * @param string[] $names
     */
    function sortHeaders($names){
        //TODO

        return $this;
    }

    protected function getSelectedFields(){
        $fields = [];
        foreach($this->columns AS $col){
            /**@var TableColumn $col**/
            $f = $col->getSelectedField();
            if($f)
                $fields[$col->name] = $f;
        }
        return array_values($fields);
    }

    /**
     * @param Request|null $request
     * @return ResourceQuery
     */
    function toResourceQuery($request=null):ResourceQuery{
        $query = $this->query->clone();

        if($this->autoSelect){
            $selectField = $this->getSelectedFields();
            $query->select($selectField);
        }

        $qr = new ResourceQueryBuilder($query,$request);
        //apply filtering/ordering
        foreach($this->columns AS $col){
            /**@var TableColumn $col**/
            $col->_apply($qr);
        }

        return $qr;
    }


    /**
     * Return headers array
     * @return array
     */
    public function getHeaders():array{
        $headers = [];
        foreach($this->columns AS $column){
            /** @var TableColumn $column */
            if($column->visible){
                $headers[]= $column->toHeader();
            }
        }
        return $headers;
    }


    /**
     * Cast to response
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $this->toResourceQuery()->toResponse($request);
    }

    public function jsonSerialize()
    {
        return $this->toResourceQuery()->jsonSerialize();
    }

    public function __toString(){
        return json_encode($this);
    }

    public function render(){
        return view('autotable.data-table',[
            'headers'=>$this->getHeaders(),
            'action'=>$this->action,
        ]);
    }

    /**
     * Set hidden fields
     * @param array|null $fields
     * @return $this
     */
    public function hiddenFields(array $fields){
        $this->hiddenFields=$fields;
        return $this;
    }
}
