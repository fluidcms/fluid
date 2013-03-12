<?php namespace Fluid\Twig;

class FieldNodeVisitor implements \Twig_NodeVisitorInterface {
	/**
	 * Called before child nodes are visited.
	 *
	 * @param Twig_NodeInterface $node The node to visit
	 * @param Twig_Environment   $env  The Twig environment instance
	 *
	 * @return Twig_NodeInterface The modified node
	 */
	public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env) {
		// For loops
		if ($node instanceof \Twig_Node_For) {
			new Field\FieldArray($node);
		}		
		return $node;
	}

	/**
	 * Called after child nodes are visited.
	 *
	 * @param Twig_NodeInterface $node The node to visit
	 * @param Twig_Environment   $env  The Twig environment instance
	 *
	 * @return Twig_NodeInterface The modified node
	 */
	public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env) {
		// Normal Variables
		if ($node instanceof \Twig_Node_Print && !$node instanceof PrintNode\FieldArrayNodePrint) {
			$node = (new Field\Field($node))->setNodePrint();
		}
		return $node;
	}

	/**
	 * Returns the priority for this visitor.
	 *
	 * Priority should be between -10 and 10 (0 is the default).
	 *
	 * @return integer The priority level
	 */
	public function getPriority() {
		return -10;
	}
}
