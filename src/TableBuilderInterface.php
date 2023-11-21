<?php
namespace plokko\TableHelper;

use Illuminate\Contracts\Support\Htmlable;
use plokko\ResourceQuery\ResourceQuery;

interface TableBuilderInterface
{
    /**
     * Adds or get a new column
     * @param string $name
     * @return TableColumnBuilder
     */
    public function column($name):TableColumnBuilder;
    /**
     * Removes a column
     * @param string $name
     * @return TableColumnBuilder
     */
    public function removeColumn($name):TableBuilder;

    public function setDefaultSortBy($attr);
    public function setBaseLangFile($attr);

    /**
     * Set field selection
     * @param $fields
     * @return mixed
     */
    public function selectFields(array $fields);

    /**
     * Set form action and method
     * @param string $action
     * @return TableBuilderInterface
     */
    public function formAction($action);

    /**
     * Set auto field selection on or off on the form
     * @param boolean $enabled
     * @return TableBuilderInterface
     */
    public function autoSelect($enabled);

    /**
     * Returns the table
     * @return string|Htmlable|\Illuminate\Contracts\View\View
     */
    public function render();
    /**
     * Returns the table attributes
     * @return string|Htmlable
     */
    public function renderAttr();
    /**
     * Returns the table body
     * @return string|Htmlable
     */
    public function renderBody();

    /**
     * Returns table headers
     * @return array
     */
    public function getHeaders():array;

    public function toResourceQuery($request = null): ResourceQuery;

    /**
     * Add a new filter or update an existing one
     * @param string $name
     * @param callable|string $condition
     * @param null $field
     * @return FilterCondition
     */
    function addFilter($name, $condition = null, $field = null):TableFilterCondition;

    /**
     * Remove a filter by name
     * @param string $name Filter name
     * @return TableBuilder
     */
    function removeFilter($name):TableBuilder;

    /**
     * Use a resource
     * @param string $name resource class name
     * @return $this
     */
    function useResource($name);

    /**
     * Set page size
     * @param int|null|array $pagination
     * @return $this
     */
    function setPageSize($size);
}
