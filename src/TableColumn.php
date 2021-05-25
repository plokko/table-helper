<?php

namespace plokko\TableHelper;


use Illuminate\Database\Query\Expression;
use plokko\ResourceQuery\ResourceQuery;

/**
 * Class TableColumn
 * @package plokko\TableHelper
 *
 * @property-read string $name
 * @property-read string $value
 * @property-read string|null $title
 * @property-read string|Expression $field
 *
 * @property-read boolean $filterable
 * @property-read boolean $sortable
 *
 * @property-read 'start'|'center'|'end'|null $align
 * @property-read boolean $divider
 * @property-read string|null $class
 * @property-read string|null $cellClass
 * @property-read int|null $width
 * @property-read string|null $type
 * @property-read string|null $component
 * @property-read boolean $visible
 *
 */
class TableColumn
{

    private
        /**@var \plokko\TableHelper\TableHelper **/
        $parent;

    protected
        $name, //unique name
        $value, //?? field?
        $title, //Header label
        $field, // SQL Field

        $filter=null, //if filterable
        $sort=null, //if filterable

        $align=null, //align header 'start' | 'center' | 'end',
        $divider=null,
        $class=null,
        $cellClass=null,
        $width=null,

        $type=null,
        $component=null,
        $visible=true,

        $select=null;


    /**
     * TableColumn constructor.
     * @param TableHelper $parent
     * @param $name
     */
    function __construct(TableHelper $parent, $name){
        $this->parent = $parent;
        $this->name = $name;
        $this->field = $name;
    }

    //VALUE?!?!?

    /**
     * Set header title for the column
     * @param string|null $title
     * @return $this
     */
    function title($title){
        $this->title = $title;
        return $this;
    }

    /**
     * Set table field
     * @param string|Expression|null $field
     * @return $this
     */
    function field($field){
        $this->field = $field;
        return $this;
    }

    /**
     * @param string|null $type
     * @return $this
     */
    function type($type){
        $this->type = $type;
        return $this;
    }
    /**
     * @param string|null $component
     * @return $this
     */
    function component($component){
        $this->component = $component;
        return $this;
    }

    /**
     * Align header, values 'start', 'center', 'end' or null
     * @param 'start'|'center'|'end'|null $align
     * @return $this
     */
    function align($align){
        $this->align = $align;
        return $this;
    }

    /**
     * Set column as visible in the headers
     * @param bool $visible
     * @return $this
     */
    function visible($visible=true){
        $this->visible = $visible;
        return $this;
    }

    /**
     * Set column divider
     * @param bool $divider
     * @return $this
     */
    function divider($divider=true){
        $this->divider = $divider;
        return $this;
    }

    /**
     * Set header class
     * @param string|null $class
     * @return $this
     */
    function class($class){
        $this->class = $class;
        return $this;
    }

    /**
     * Set header cellClass
     * @param string|null $cellClass
     * @return $this
     */
    function cellClass($cellClass){
        $this->cellClass = $cellClass;
        return $this;
    }

    /**
     * Set header width
     * @param int|null $width
     * @return $this
     */
    function width($width){
        $this->width = $width;
        return $this;
    }

    /**
     * Set column as hidden in the headers
     * @param bool $hidden
     * @return $this
     */
    function hidden($hidden=true){
        return $this->visible(!$hidden);
    }


    /**
     * Enable filtering for this column
     * @param string|null $condition
     * @param null|string $field
     * @return $this
     */
    function filter($condition='=',$field=null){
        if($condition==null || $condition==false)
            $this->filter=null;
        else{
            $this->filter = [
                'condition' => $condition,
                'field' => $field,
            ];
        }
        return $this;
    }

    /**
     * Enable sorting for this column
     * @param bool|string|\Illuminate\Database\Query\Expression $field false to disable sorting, true to enable using column field, string or \Illuminate\Database\Query\Expression for sorting this filed
     * @param false $inverted
     */
    function sort($field=true,$inverted=false){
        if($field===null || $field === false)
            $this->sort = null;
        else{
            $this->sort = [
                'field'=> $field===true?null:$field,
                'inverted' => $inverted,
            ];
        }
        return $this;
    }

    /**
     * Remove this field
     * @return TableHelper
     */
    function remove(){
        $this->parent->removeColumn($this->name);
        return $this->parent;
    }

    /**
     * @return bool
     */
    function isFilterable(){
        return $this->filter !==null;
    }

    /**
     * @return bool
     */
    function isSortable(){
        return $this->sort !==null;
    }

    /**
     * @return bool
     */
    function isVisible(){
        return $this->visible;
    }



    function __get($k){

        if($k=== 'filterable')
            return $this->isFilterable();
        if($k=== 'sortable')
            return $this->isSortable();
        else if(in_array($k,[
                'name',
                'value',
                'title',
                'field',

                'align',
                'divider',
                'class',
                'cellClass',
                'width',
                'type',
                'component',
                'visible',])){
            return $this->$k;
        }
    }

    /**
     * Fallback to parent
     * @param $fn
     * @param $args
     * @return false|mixed
     */
    function __call($fn,$args){
        return call_user_func_array([$this->parent,$fn],$args);
    }


    /**
     * @param string $name
     * @return TableColumn
     */
    function column($name,$field=null,$label=null):TableColumn{
        return $this->parent->column($name,$field,$label);
    }

    function toHeader(){
        $headers = [
            'text'=>$this->title?: $this->name,
            'value'=>$this->value?: $this->name, //or 'field'=>$this->field, ?
            'filterable'=>$this->isFilterable(), //<<todo!
            'sortable'=>$this->isSortable(),
        ];

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
        }

        return $headers;
    }

    /**
     * @private
     * @param ResourceQuery $query
     */
    function _apply(ResourceQuery $query){
        if(!$this->visible)
            return;
        $_field = $this->field?:$this->name;
        if($this->filter){
            $cnd = $this->filter['condition'];
            $field = $this->filter['field']?? $_field;
            $query->filter($this->name,$cnd,$field);
        }
        if($this->sort){
            $field = $this->sort['field']?? $_field;
            $inverted = $this->sort['inverted'];
            $query
                ->orderBy($this->name,$field)
                ->invert($inverted);
        }
    }

    /**
     * @private
     * @return \Illuminate\Database\Query\Expression|mixed|null
     */
    function getSelectedField(){
        if($this->select===false || $this->select===null){
            return null;
        }
        if($this->select===true){
            if(!$this->field)
                return null;
            if($this->field instanceof  \Illuminate\Database\Query\Expression ){
                $v = $this->field->getValue();
                if(preg_match('/(.*) AS (.*)$/i',$v,$match)){
                    $v = $match[1];
                }
                return \DB::raw($v.' AS `'.$this->name.'`');
            }
            return $this->field;
        }
        return $this->select;
    }

    /**
     * Set the selected table field for autoselect (if enabled)
     * @param bool|string|\Illuminate\Database\Query\Expression $field if null or false nothing will be selected, if true the column field or name will be used, otherwise the specified parameter will be used
     * @return $this
     */
    function select($field=true){
        $this->select=$field;
        return $this;
    }
}
