<?php namespace Fluid\Twig\Field;

class FieldOutput {
	private static $fluidVariables = array('page', 'site', 'section', 'sections');
	
	public static function returnFields() {
		self::outputFields();
		self::outputFieldArrays();
	}
	
	private static function outputFields() {
		foreach(Field::getInstaces() as $field) {
			$fieldOutput = $field->output();
			$key = explode('.', $fieldOutput['key'], 2);
			
			if (in_array($key[0], self::$fluidVariables)) {
				echo '<script>'.json_encode($fieldOutput).'</script>';
			}
		}
	}
	
	private static function outputFieldArrays() {
		foreach(FieldArray::getInstaces() as $fieldArray) {
			$fieldOutput = $fieldArray->output();
			$key = explode('.', $fieldOutput['expression'], 2);
			
			if (in_array($key[0], self::$fluidVariables)) {
				echo '<script>'.json_encode($fieldOutput).'</script>';
			}
		}
	}
}