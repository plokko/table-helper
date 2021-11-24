<?php
namespace plokko\TableHelper;

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
}