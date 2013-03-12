<?php namespace Fluid\Twig\Field;

interface FieldInterface {
	public function output();
	public function getId();
	public function getNode( $name );
	public static function getInstaces();
}