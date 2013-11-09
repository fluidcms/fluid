<?php

namespace Fluid\Twig\PrintNode;

use Fluid\Twig\Field\Field;
use Twig_Compiler;

class FieldNodePrint extends \Twig_Node_Print
{
    /**
     * Add an item to a field array
     *
     * @param Twig_Compiler $compiler
     */
    public function compile(Twig_Compiler $compiler)
    {
        $field = Field::matchNode($this->getNode('expr')->getNode('node'));

        if (null !== $field) {
            $compiler
                ->addDebugInfo($this)
                ->write('\Fluid\Twig\Field\Field::setValue("' . $field . '", str_replace("\"", "#34;", ')
                ->subcompile($this->getNode('expr'))
                ->write('))')
                ->raw(";\n");;
        }

        $compiler
            ->addDebugInfo($this)
            ->write('echo ')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n");
    }
}
