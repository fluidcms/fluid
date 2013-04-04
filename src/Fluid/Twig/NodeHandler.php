<?php namespace Fluid\Twig;

class NodeHandler {	
	/**
	 * Get an expression name from a node
	 * 
	 * @param   mixed   $node
	 * @return	string
	 */
	public static function getExpressionName( $node ) {
		if ($node instanceof \Twig_Node_Expression_Name) return $node->getAttribute('name');
		else if ($node instanceof \Twig_Node_Expression_GetAttr) return self::getExpressionArrayName($node);
		else if ($node instanceof \Twig_Node_Expression_Filter_Default) return self::getExpressionArrayName($node->getNode('node')->getNode('expr1'));
		else if ($node instanceof \Twig_Node_Expression_Filter) {
			return self::getExpressionName($node->getNode('node'));
		}
	}
	
	private static function getExpressionArrayName( $node ) {
		$name = '';
				
		foreach($node as $childNode) {
			if ($childNode instanceof \Twig_Node_Expression_Constant) $name .= $childNode->getAttribute('value') . '.';
			else if ($childNode instanceof \Twig_Node_Expression_Name) $name .= $childNode->getAttribute('name') . '.';
			else if (count($childNode)) $name .= self::getExpressionArrayName($childNode) . '.';
		}
		
		return substr($name, 0, -1);
	}
}