<?php
namespace Fluid\Variable;

use Fluid\MappingInterface;
use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;

/**
 * todo redo this entire class, its a mess of patching over patching
 */
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
     * @param MappingInterface $mapping
     * @param VariableCollection $variables
     * @return VariableCollection
     */
    public function mapXmlObject(MappingInterface $mapping, VariableCollection $variables)
    {
        foreach ($mapping->getContent() as $key => $value) {
            if (isset($value['name']) && $value['name'] === 'variable') {
                $attributes = isset($value['attributes']) ? $value['attributes'] : [];
                $variable = new VariableEntity($this->registry);
                $variable->set($attributes);
                $variables->addVariable($variable);
            } elseif (isset($value['name']) && $value['name'] === 'group') {
                $attributes = isset($value['attributes']) ? $value['attributes'] : [];
                $variableGroup = new VariableGroup($this->registry);
                $variableGroup->setName(isset($attributes['name']) ? $attributes['name'] : null);
                foreach ($value as $groupVariable) {
                    if (isset($groupVariable['name']) && $groupVariable['name'] === 'variable') {
                        $attributes = isset($groupVariable['attributes']) ? $groupVariable['attributes'] : [];
                        $variable = new VariableEntity($this->registry);
                        $variable->set($attributes);
                        $variableGroup->add($variable);
                    }
                }
                $variables->addVariable($variableGroup);
            } elseif (isset($value['name']) && $value['name'] === 'image') {
                $variable = new VariableEntity($this->registry);
                $attributes = [];
                $formats = [];
                foreach ($value as $attributeKey => $attributeValue) {
                    if ($attributeKey === 'attributes') {
                        if (isset($attributeValue['name'])) {
                            $variable->setName($attributeValue['name']);
                        }
                        if (isset($attributeValue['format'])) {
                            $attributes['format'] = $attributeValue['format'];
                        }
                        if (isset($attributeValue['width'])) {
                            $attributes['width'] = $attributeValue['width'];
                        }
                        if (isset($attributeValue['height'])) {
                            $attributes['height'] = $attributeValue['height'];
                        }
                    } elseif (isset($attributeValue['name']) && $attributeValue['name'] === 'format' && isset($attributeValue['attributes'])) {
                        $format = [];
                        foreach ($attributeValue['attributes'] as $subAttributeKey => $subAttributeValue) {
                            if ($subAttributeKey === 'name') {
                                $format['name'] = $subAttributeValue;
                            }
                            if ($subAttributeKey === 'format') {
                                $format['attributes']['format'] = $subAttributeValue;
                            }
                            if ($subAttributeKey === 'width') {
                                $format['attributes']['width'] = $subAttributeValue;
                            }
                            if ($subAttributeKey === 'height') {
                                $format['attributes']['height'] = $subAttributeValue;
                            }
                        }
                        $formats[] = $format;
                    }
                }
                $variable->setAttributes($attributes);
                $variable->setFormats($formats);
                $variables->addVariable($variable);
            }
        }
        return $variables;
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
                    } else {
                        // todo switch to mapCollectionValues (below)
                        trigger_error('Switch to mapCollectionValues (below)');
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
        } elseif ($attributes['type'] === 'image') {
            $varAttributes = $variable->getAttributes();
            if (isset($attributes['attributes']['src'])) {
                $varAttributes['src'] = $attributes['attributes']['src'];
            }
            if (isset($attributes['attributes']['alt'])) {
                $varAttributes['alt'] = $attributes['attributes']['alt'];
            }
            if (isset($attributes['attributes']['width']) && !isset($varAttributes['width'])) {
                $varAttributes['width'] = $attributes['attributes']['width'];
            }
            if (isset($attributes['attributes']['height']) && !isset($varAttributes['height'])) {
                $varAttributes['height'] = $attributes['attributes']['height'];
            }
            $variable->setAttributes($varAttributes);

            $varFormats = $variable->getFormats();
            if (isset($attributes['formats'])) {
                foreach ($varFormats as $key => $varFormat) {
                    foreach ($attributes['formats'] as $format) {
                        if (isset($format['name']) && isset($varFormat['name']) && $format['name'] === $varFormat['name']) {
                            if (isset($format['attributes']['src'])) {
                                $varFormat['attributes']['src'] = $format['attributes']['src'];
                            }
                            if (isset($format['attributes']['alt'])) {
                                $varFormat['attributes']['alt'] = $format['attributes']['alt'];
                            }
                            if (isset($format['attributes']['width']) && !isset($varFormat['attributes']['width'])) {
                                $varFormat['attributes']['width'] = $format['attributes']['width'];
                            }
                            if (isset($format['attributes']['height']) && !isset($varFormat['attributes']['height'])) {
                                $varFormat['attributes']['height'] = $format['attributes']['height'];
                            }
                        }
                    }
                    $varFormats[$key] = $varFormat;
                }
            }

            $variable->setFormats($varFormats);
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