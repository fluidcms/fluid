<?php namespace Fluid\Twig\Field;

use Fluid\Twig\PrintNode\FieldArrayNodePrint, Fluid\Twig\NodeHandler;

class FieldArray implements FieldInterface {
	private $node;
	
	private $enter;
	
	private static $instances = array();
	
	private $expressionName, $keyName, $valueName;
	
	private $items = array();
	
	private $variables = array();
	
	private $id;
	
	public function __construct( \Twig_Node_For $node ) {
		$this->order = Field::getOrder();
		$this->node = $node;
		$this->expressionName = $this->getExpressionName($node->getNode('seq'));
		$this->keyName = $this->getKeyName($node->getNode('value_target'));
		$this->valueName = $this->getValueName($node->getNode('value_target'));
		$this->id = uniqid();
		self::$instances[$this->id] = $this;
		$this->setNodePrint();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getNode( $name ) {
		return $this->node->getNode($name);
	}
	
	private function getExpressionName( $node ) {
		return NodeHandler::getExpressionName($node);
	}
	
	private function getKeyName( \Twig_Node_Expression_AssignName $node ) {
		return $node->getAttribute('name');
	}
	
	private function getValueName( \Twig_Node_Expression_AssignName $node ) {
		return $node->getAttribute('name');
	}
	
	public static function getInstaces() {
		return self::$instances;
	}
	
	public static function setItem( $id, $key, $value ) {
		self::$instances[$id]->_setItem($key, $value);
	}
	
	public function _setItem( $key, $value ) {
		$this->items[] = [$key, $value];
	}
	
	public static function setVariable( $id, $key ) {
		self::$instances[$id]->_setVariable($key);
	}
	
	public function _setVariable( $key ) {
		$this->variables[] = $key;
	}
	
	/**
	 * Replace default Twig Node Print with Fluid Array Node Print
	 * 
	 * @return	void
	 */
	private function setNodePrint() {
		$baseNode = $this->node->getNode('body')->getNode(0);
		
		for ($i = 0; $i < count($baseNode); $i++) {
			if ($baseNode->getNode($i) instanceof \Twig_Node_Print) {
				$node = $this->node->getNode('body')->getNode(0)->getNode($i);
				$node = new FieldArrayNodePrint($node->getNode('expr'), $node->getLine(), $node->getNodeTag());
				$this->node->getNode('body')->getNode(0)->setNode($i, $node);
			}
		}
	}
	
	/**
	 * Match a Print Node with a FieldArray object and return the FieldArray object ID
	 * 
	 * @param   Twig_Node_Expression_GetAttr	$node
	 * @return	string
	 */
	public static function matchNode( \Twig_Node_Expression_GetAttr $node ) {
		foreach(self::$instances as $instance) {
			$baseNode = $instance->getNode('body')->getNode(0);
			
			for ($i = 0; $i < count($baseNode); $i++) {
				if ($baseNode->getNode($i) instanceof FieldArrayNodePrint && $baseNode->getNode($i)->getNode('expr')->getNode('node') == $node) {
					return $instance->getId();
				}
			}
		}
	}
	
	public function output() {
		$array = array();
		$array['order'] = $this->order;
		$array['type'] = 'array';
		$array['expression'] = $this->expressionName;
		$array['key'] = $this->valueName;
		
		$array['variables'] = $this->variables;
		
		$array['items'] = array();
		$count = 0;
		$resetKey = null;
		foreach($this->items as $item) {
			if ($resetKey === null) {
				$resetKey = $item[0];
			}
			if ($item[0] == $resetKey) {
				$count++;
			} 
			$array['items'][$count][$item[0]] = $item[1];
		}
		
		return $array;
	}
}