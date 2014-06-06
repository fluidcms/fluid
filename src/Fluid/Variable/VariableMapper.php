<?php
namespace Fluid\Variable;

use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;

class VariableMapper
{
    const DATA_DIRECTORY = 'pages';

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param VariableCollection $collection
     */
    public function persist(VariableCollection $collection)
    {
        $file = $this->getFile($collection->getPage(), $this->registry->getLanguage()->getLanguage());
        $this->registry->getStorage()->saveBranchData($file, $collection->toArray());
    }

    /**
     * @param VariableCollection $collection
     */
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();
        $file = $this->getFile($collection->getPage(), $this->registry->getLanguage()->getLanguage());
        $data = $this->registry->getStorage()->loadBranchData($file);

        if (is_array($data)) {
            foreach ($data as $item) {
                $variable = $variables->find($item['name']);
                if ($variable) {
                    if (isset($item['value'])) {
                        $variable->setValue($item['value']);
                    } elseif (isset($item['variables'])) {
                        $variable->reset($item['variables']);
                    }
                }
            }
        }
    }

    /**
     * @param VariableCollection $collection
     * @param array $variables
     * @return VariableCollection
     */
    public function mapCollectionValues(VariableCollection $collection, array $variables)
    {
        foreach ($variables as $data) {
            if (isset($data['name'])) {
                if ($variable = $collection->find($data['name'])) {
                    $this->mapVariableValue($variable, $data);
                }
            }
        }
        return $collection;
    }

    /**
     * @param VariableEntity $variable
     * @param array $attributes
     * @return VariableEntity
     */
    public function mapVariableValue(VariableEntity $variable, array $attributes)
    {
        if ($attributes['type'] === 'string') {
            $variable->setValue(isset($attributes['value']) ? $attributes['value'] : null);
        }
        return $variable;
    }

    /**
     * @param PageEntity $page
     * @param string $language
     * @return string
     */
    private function getFile(PageEntity $page, $language)
    {
        $filepath = '';
        $parent = $page->getParent();
        while ($parent) {
            $filepath .= DIRECTORY_SEPARATOR . $parent->getName();
            $parent = $parent->getParent();
        }

        $filepath .= DIRECTORY_SEPARATOR . $page->getName();
        return self::DATA_DIRECTORY . $filepath . '_' . $language . '.json';
    }
}