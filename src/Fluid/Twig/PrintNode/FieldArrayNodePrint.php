<?php namespace Fluid\Twig\PrintNode;

use Fluid\Twig\Field\FieldArray, Fluid\Twig\NodeHandler;

class FieldArrayNodePrint extends \Twig_Node_Print {	
	/**
	 * Add an item to a field array
	 *
	 * @param   Twig_Compiler  $compiler
	 * @return  void
	 */
	public function compile(\Twig_Compiler $compiler) {
		$fieldArray = FieldArray::matchNode($this->getNode('expr')->getNode('node'));
		
		FieldArray::setVariable($fieldArray, NodeHandler::getExpressionName($this->getNode('expr')->getNode('node')));
						
		$compiler
			->addDebugInfo($this)
			->write('\Fluid\Twig\Field\FieldArray::setItem("'.$fieldArray.'", "'.NodeHandler::getExpressionName($this->getNode('expr')->getNode('node')).'", str_replace("\"", "#34;", ')
			->subcompile($this->getNode('expr'))
			->write('))')
			->raw(";\n")
		;
        $compiler
            ->addDebugInfo($this)
            ->write('echo ')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
        ;
	}
}
