<?php

namespace plokko\TableHelper;

use Illuminate\Contracts\Support\Responsable;
use plokko\ResourceQuery\ResourceQueryBuilder;

/**
 * @method TableFilterCondition field(string $name)
 * @method TableFilterCondition condition(string|callable $condition)
 * @method TableFilterCondition defaultValue($value) Set filter default value (used if none are specified)
 * @method TableFilterCondition formatValue(callable|null $formatter) Format the filtered value
 * @method TableFilterCondition applyIf(callable $cnd) Apply this filter only if condition is met
 * @method TableFilterCondition applyIfPresent(string|array ...$name) Only apply this condition if all the specified filters are present on the query
 * @method TableFilterCondition applyIfNotPresent(string|array ...$name) Only apply this condition if all the filters specifiead are NOT present on the query

 */
class TableFilterCondition  implements TableBuilderInterface,\Illuminate\Contracts\Support\Arrayable,\JsonSerializable,Responsable
{
    use TableBuilderColumnTrait;
    private
        $name = null,
        $parent,
        $calls = [];

    function __construct(TableBuilder $parent,string $name,$field=null)
    {
        $this->name = $name;
        $this->parent = $parent;

        $this->field($name);
    }

    /**
     * Remove itself from filter parameters
     * @return FilterBuilder
     */
    function remove(){
        $this->parent->remove($this->name);
        return $this->parent;
    }


    function __call($fn,$args){
        /*
        if(in_array($fn, [
                'field',
                'condition',
                'defaultValue',
                'formatValue',
                'applyIf',
                'applyIfPresent',
                'applyIfNotPresent',
            ])){
            $this->calls[$fn] = $args;
        }
        //*/
        $this->calls[] = ['fn'=>$fn,'args'=>$args];
        return $this;
    }

    /**
     * @internal
     * @param ResourceQueryBuilder $rq
     * @return false|mixed|void
     */
    function _applyToResourceQuery(ResourceQueryBuilder $rq){
        $filter = $rq->filter($this->name);
        foreach($this->calls AS $e){
            call_user_func_array([$filter,$e['fn']],$e['args']);
        }
    }
}
