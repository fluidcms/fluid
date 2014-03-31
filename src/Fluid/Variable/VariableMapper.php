<?php
namespace Fluid\Variable;

class VariableMapper
{
    /**
     * @param VariableCollection $collection
     */
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();
        var_dump($collection);
    }
}