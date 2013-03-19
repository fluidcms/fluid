<?php namespace Fluid\Twig\Field;

use Fluid\Twig\PrintNode\FieldNodePrint, Fluid\Twig\NodeHandler;

class Field implements FieldInterface {
	private $node;
	
	private $enter;
	
	private $key, $value;
	
	private static $instances = array();
	
	private $id;

	private static $_order = 0;
	
	/**
	 * Get the current order of the field and increment it
	 * 
	 * @param   mixed   $node
	 * @return  int
	 */
	public static function getOrder() {
		return self::$_order++;
	}
	
	public function __construct( \Twig_Node_Print $node ) {
		$this->order = Field::getOrder();
		$this->node = $node;
		$this->id = uniqid();
		$this->key = NodeHandler::getExpressionName($this->getNode('expr'));
		self::$instances[$this->id] = $this;
	}
	
	public function setNodePrint() {
		$node = new FieldNodePrint($this->node->getNode('expr'), $this->node->getLine(), $this->node->getNodeTag());
		return $node;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getNode( $name ) {
		return $this->node->getNode($name);
	}
	
	public static function getInstaces() {
		return self::$instances;
	}
	
	public static function setValue( $id, $value ) {
		self::$instances[$id]->_setValue($value);
	}
	
	public function _setValue( $value ) {
		$this->value = $value;
	}
		
	/**
	 * Match a Print Node with a FieldArray object and return the FieldArray object ID
	 * 
	 * @param   mixed   $node
	 * @return	string
	 */
	public static function matchNode( $node ) {
		foreach(self::$instances as $instance) {
			if($instance->getNode('expr') == $node) {
				return $instance->getId();
			}
		}
	}
	
	public function output() {
		$array = array();
		$array['order'] = $this->order;
		$array['type'] = 'variable';
		$array['key'] = $this->key;
		$array['value'] = $this->value;
		
		return $array;
	}
}