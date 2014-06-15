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
                foreach ($value as $groupVariableKey => $groupVariable) {
                    if ($groupVariableKey !== 'attributes' && $groupVariableKey !== 'name') {
                        $variable = $this->mapXmlItem($groupVariable);
                        if ($variable) {
                            $variableGroup->add($variable);
                        }
                    }
                }
                $variables->addVariable($variableGroup);
            } elseif (isset($value['name']) && $value['name'] === 'array') {
                // todo switch to mapXmlArray (below)
                trigger_error('Switch to mapXmlArray (below)');
            } elseif (isset($value['name']) && $value['name'] === 'image') {
                $variable = new VariableEntity($this->registry);
                $variable->setType(VariableEntity::TYPE_IMAGE);
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
     * @param array $xml
     * @return VariableArray|VariableEntity|null
     */
    public function mapXmlItem($xml)
    {
        if (isset($xml['name']) && $xml['name'] === 'variable') {
            return $this->mapXmlVariable($xml);
        } elseif (isset($xml['name']) && $xml['name'] === 'array') {
            return $this->mapXmlArray($xml);
        }
        return null;
    }

    /**
     * @param array $xml
     * @return VariableEntity
     */
    public function mapXmlVariable($xml)
    {
        $variable = new VariableEntity($this->registry);
        $variable->set($xml['attributes']);
        return $variable;
    }

    /**
     * @param array $xml
     * @return VariableArray|null
     */
    public function mapXmlArray($xml)
    {
        if (isset($xml['attributes']['name'])) {
            $variable = new VariableArray($this->registry);
            $variable->setName($xml['attributes']['name']);
            foreach ($xml as $key => $value) {
                if ($key !== 'attributes' && $key !== 'name') {
                    $var = $this->mapXmlVariable($value);
                    if ($var) {
                        $variable->addVariable($var);
                    }
                }
            }
            return $variable;
        }

        return null;
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
                    if ($variable instanceof VariableEntity) {
                        $this->mapJsonVariable($variable, $item);
                    } elseif ($variable instanceof VariableGroup) {
                        $this->mapJsonGroup($variable, $item);
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
     * @param array $json
     * @return VariableCollection
     */
    public function mapJsonCollection(VariableCollection $collection, array $json)
    {
        foreach ($json as $data) {
            if (isset($data['name'])) {
                if ($variable = $collection->find($data['name'])) {
                    $this->mapJsonVariable($variable, $data);
                }
            }
        }
        return $collection;
    }

    /**
     * @param VariableGroup $group
     * @param array $json
     * @return VariableGroup
     */
    public function mapJsonGroup(VariableGroup $group, array $json)
    {
        $array = $json['variables'];
        $json['variables'] = [];
        foreach ($array as $key => $value) {
            $json['variables'][$value['name']] = $value;
        }

        foreach ($group->getVariables() as $variable) {
            if (isset($json['variables'][$variable->getName()])) {
                if ($variable instanceof VariableEntity) {
                    $this->mapJsonVariable($variable, $json['variables'][$variable->getName()]);
                } elseif ($variable instanceof VariableArray) {
                    $this->mapJsonArray($variable, $json['variables'][$variable->getName()]);
                }
            }
        }

        return $group;
    }

    /**
     * @param VariableArray $array
     * @param array $json
     * @return VariableArray
     */
    public function mapJsonArray(VariableArray $array, array $json)
    {
        if (isset($json['variables'])) {
            foreach ($json['variables'] as $item) {
                $item = $this->mapJsonArrayItem($array->getVariables(), $item);
                $array->addItem($item);
            }
        }
        return $array;
    }

    /**
     * @param VariableEntity[] $variables
     * @param array $json
     * @return VariableEntity[]
     */
    public function mapJsonArrayItem(array $variables, array $json)
    {
        $data = [];
        foreach ($json as $value) {
            $data[$value['name']] = $value;
        }

        $retval = new VariableArrayItem();
        foreach ($variables as $prototype) {
            $variable = clone($prototype);
            if (isset($data[$variable->getName()])) {
                $variable = $this->mapJsonVariable($variable, $data[$variable->getName()]);
            }
            $retval->add($variable);
        }

        return $retval;
    }

    /**
     * @param VariableEntity $variable
     * @param array $json
     * @return VariableEntity
     */
    public function mapJsonVariable(VariableEntity $variable, array $json)
    {
        if ($json['type'] === 'string') {
            $variable->setValue(isset($json['value']) ? $json['value'] : null);
        } elseif ($json['type'] === 'content') {
            $variable->setValue(isset($json['value']) ? $json['value'] : null);
        } elseif ($json['type'] === 'image') {
            $varAttributes = $variable->getAttributes();
            if (isset($json['attributes']['src'])) {
                $varAttributes['src'] = $json['attributes']['src'];
            }
            if (isset($json['attributes']['alt'])) {
                $varAttributes['alt'] = $json['attributes']['alt'];
            }
            if (isset($json['attributes']['width']) && !isset($varAttributes['width'])) {
                $varAttributes['width'] = $json['attributes']['width'];
            }
            if (isset($json['attributes']['height']) && !isset($varAttributes['height'])) {
                $varAttributes['height'] = $json['attributes']['height'];
            }
            $variable->setAttributes($varAttributes);

            $varFormats = $variable->getFormats();
            if (isset($json['formats'])) {
                foreach ($varFormats as $key => $varFormat) {
                    foreach ($json['formats'] as $format) {
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