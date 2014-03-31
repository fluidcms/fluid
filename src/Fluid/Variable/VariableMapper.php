<?php
namespace Fluid\Variable;

use Fluid\Language\LanguageEntity;

class VariableMapper
{
    const DATA_DIRECTORY = 'pages';

    public function __construct(LanguageEntity $language)
    {

    }

    /**
     * @param VariableCollection $collection
     */
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();

        $file = self::DATA_DIRECTORY . DIRECTORY_SEPARATOR . $collection->getPage()->getName();
        var_dump($file);
    }
}