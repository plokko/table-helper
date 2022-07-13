<?php
namespace plokko\TableHelper;

use Illuminate\Contracts\Support\Htmlable;
use plokko\ResourceQuery\ResourceQuery;

trait TableBuilderColumnTrait
{
    /**
     * @var TableBuilder $parent
     */

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

    public function formAction($action)
    {
        $this->parent->formAction($action);
        return $this;
    }

    public function autoSelect($enabled)
    {
        $this->parent->autoSelect($enabled);
        return $this;
    }

    public function toResponse($request)
    {
        return $this->parent->toResponse($request);
    }

    public function render()
    {
        return $this->parent->render();
    }

    public function renderAttr()
    {
        return $this->parent->renderAttr();
    }

    public function renderBody()
    {
        return $this->parent->renderBody();
    }

    public function getHeaders(): array
    {
        return $this->parent->getHeaders();
    }

    public function toResourceQuery($request = null): ResourceQuery{
        return $this->parent->toResourceQuery($request);
    }

    public function toArray()
    {
        return $this->parent->toArray();
    }

    public function jsonSerialize()
    {
        return $this->parent->jsonSerialize();
    }

    public function __toString(){
        return json_encode($this);
    }
    /**
     * Add a new filter or update an existing one
     * @param string $name
     * @param callable|string $condition
     * @param null $field
     * @return FilterCondition
     */
    function addFilter($name, $condition = null, $field = null): TableFilterCondition{
        return $this->parent->addFilter($name, $condition, $field );
    }

    /**
     * Remove a filter by name
     * @param string $name Filter name
     * @return $this
     */
    function removeFilter($name):TableBuilder{
        return $this->parent->removeFilter($name);
    }
    
    /**
     * Remove a filter by name
     * @param string $name Filter name
     * @return $this
     */
    function useResource($name)
    {
        $this->parent->useResource($name);
        return $this;
    }
}
