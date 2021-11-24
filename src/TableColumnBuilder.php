<?php
namespace plokko\TableHelper;

use DB;
use JsonSerializable;
use Illuminate\Contracts\Support\Responsable;
use plokko\ResourceQuery\ResourceQuery;

/**
 * @property-read $name
 * @property-read boolean $sortable
 * @property-read boolean $filterable
 * @property $label
 * @property $field
 * @property $visible
 */
class TableColumnBuilder implements TableBuilderInterface,\Illuminate\Contracts\Support\Arrayable,JsonSerializable,Responsable
{
    /**
     * @var TableBuilder
     */
    private $parent;
    protected
        $name,
        $field=null,
        $label=null,
        $visible=true,
        $virtual=false,//Does not have a table column

        $filter=null,
        $sort=null,
        $attr=[];

    function __construct(TableBuilder $parent,$name)
    {
        $this->name = $name;
        $this->parent = $parent;
    }


    /**
     * Set column label
     * @param string|null $label
     * @return $this
     */
    public function label($label){
        $this->label = $label;
        return $this;
    }
    public function field($field){
        $this->field = $field;
        return $this;
    }

    /**
     * @param boolean|string|\Illuminate\Database\Query\Expression $field
     * @param boolean $reverse
     * @return $this
     */
    public function sort($field,$reverse=false){
        if($field === false || $field === null){
            $this->sort = null;
        }else{
            $this->sort = [
                'field' => $field===true?$this->name:$field,
                'reverse' => $reverse,
            ];
        }
        return $this;
    }

    /**
     * Enable filtering for this column
     * @param string|boolean|Closure $condition
     * @param null|\Illuminate\Database\Query\Expression $field
     * @return $this
     */
    function filter($condition='=',$field=null){
        if($condition==null || $condition==false)
            $this->filter = null;
        else{
            $this->filter = [
                'condition' => $condition===true?'=':$condition,
                'field' => $field,
            ];
        }
        return $this;
    }

    function setAttrs(array $attributes){
        $this->attr = $attributes;
        return $this;
    }

    function virtual($virtual=true){
        $this->virtual = $virtual;
        return $this;
    }

    /**
     * Set field visibility
     * @param boolean $visible
     * @return $this
     */
    function visible($visible=true){
        $this->visible = true;
        return $this;
    }

    /// Getter and setter
    function __get($k){
        if($k==='sortable')
            return !!$this->sort;
        if($k==='filterable')
            return !!$this->filter;
        if(in_array($k,['label','name','field','visible']))
            return $this->$k;
    }
    function __set($k,$v){
        if(in_array($k,['label','field','visible']))
            $this->$k($v);
    }

    function getSelectedField(){
        if($this->virtual)
            return false;
        if($this->field){
            //If query expression add an alias
            if($this->field instanceof  \Illuminate\Database\Query\Expression  ){
                $v = $this->field->getValue();
                if(preg_match('/(.*) AS (.*)$/i',$v,$match)){
                    $v = $match[1];
                }
                return DB::raw($v.' AS `'.$this->name.'`');
            }
            //return field
            return $this->field;
        }
        return $this->name;//Default select 'name' as field (ex. column "name" select: "name")
    }

    public function toArray()
    {
        $data = [];//TODO
        return $data;
    }

    /// Execute from parent

    /**
     * @param string $name
     * @return TableColumnBuilder
     */
    public function column($name):TableColumnBuilder
    {
        return $this->parent->column($name);
    }

    /**
     * @param string $name
     * @return TableBuilder
     */
    public function removeColumn($name):TableBuilder
    {
        return $this->parent->removeColumn($name);
    }

    public function setDefaultSortBy($attr)
    {
        $this->parent->setDefaultSortBy($attr);
        return $this;
    }

    public function setBaseLangFile($attr)
    {
        $this->parent->setBaseLangFile($attr);
        return $this;
    }

    public function selectFields(array $fields)
    {
        $this->parent->selectFields($fields);
        return $this;
    }


    /**
     * Set form action and method
     * @param string $action
     * @return $this
     */
    public function formAction($action)
    {
        $this->parent->formAction($action);
        return $this;
    }

    /**
     * Set auto field selection on or off on the form
     * @param boolean $enabled
     * @return $this
     */
    public function autoSelect($enabled)
    {
        $this->parent->autoSelect($enabled);
        return $this;
    }

    public function __toString(){
        return json_encode($this);
    }

    public function jsonSerialize()
    {
        return $this->parent->jsonSerialize();
    }

    public function toResponse($request)
    {
        return $this->parent->toResponse($request);
    }

    /**
     * @private
     * @param ResourceQuery $query
     */
    function __apply(ResourceQuery $query){
        $_field = $this->name;//$this->field?:$this->name;

        if($this->filter){
            $cnd = $this->filter['condition'];
            $field = $this->filter['field']?: $_field;
            $query->filter($this->name,$cnd,$field);
        }
        if($this->sort){
            $field = $this->sort['field']?: $_field;
            $reverse = $this->sort['reverse'];
            $query
                ->orderBy($this->name,$field)
                ->invert($reverse);
        }
    }

    function toHeader(array $opt=[]){
        $headers = array_merge($this->attr,[
                'text'=>$this->label?: (empty($opt['label'])?$this->name:$opt['label']),
                'value'=>$this->name, //or 'field'=>$this->field, ?
                'filterable'=>$this->filterable,
                'sortable'=>$this->sortable,
            ]);
        /*
        foreach([
                    'align',
                    'divider',
                    'class',
                    'cellClass',
                    'width',
                    'type',
                    'component',
                ] AS $k){
            if($this->$k!==null)
                $headers[$k] = $this->$k;
        }*/

        return $headers;
    }

}