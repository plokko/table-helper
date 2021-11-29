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
}