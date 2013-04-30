<?php

namespace Fluid\Twig\Field;

class FieldOutput
{
    private static $fluidVariables = array('page', 'site', 'section', 'sections');

    public static function returnFields()
    {
        return array_merge(
            self::outputFields(),
            self::outputFieldArrays()
        );
    }

    private static function outputFields()
    {
        $output = array();

        foreach (Field::getInstaces() as $field) {
            $fieldOutput = $field->output();
            $key = explode('.', $fieldOutput['key'], 2);

            if (in_array($key[0], self::$fluidVariables)) {
                $output[] = $fieldOutput;
            }
        }

        return $output;
    }

    private static function outputFieldArrays()
    {
        $output = array();

        foreach (FieldArray::getInstaces() as $fieldArray) {
            $fieldOutput = $fieldArray->output();
            $key = explode('.', $fieldOutput['expression'], 2);

            if (in_array($key[0], self::$fluidVariables)) {
                $output[] = $fieldOutput;
            }
        }

        return $output;
    }
}