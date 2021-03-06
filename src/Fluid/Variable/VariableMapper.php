<?php
namespace Fluid\Variable;

use Fluid\MappingInterface;
use Fluid\RegistryInterface;

class VariableMapper
{
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
        $file = $this->registry->getPageMapper()->getFile($collection->getPage(), $this->registry->getLanguage()->getLanguage());
        $this->registry->getStorage()->saveBranchData($file, $collection->toArray());
    }

    /**
     * @param MappingInterface $mapping
     * @param VariableCollection $variables
     * @return VariableCollection
     * todo rename to mapXmlCollection
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
                $variable = $this->mapXmlImage($value);
                if ($variable) {
                    $variables->addVariable($variable);
                }
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
     * @return VariableImage
     */
    public function mapXmlImage($xml)
    {
        $variable = new VariableImage($this->registry);
        $attributes = [];
        $formats = [];
        foreach ($xml as $attributeKey => $attributeValue) {
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
        return $variable;
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
                    if (isset($value['name']) && $value['name'] === 'variable') {
                        $var = $this->mapXmlVariable($value);
                    } elseif (isset($value['name']) && $value['name'] === 'image') {
                        $var = $this->mapXmlImage($value);
                    } else {
                        trigger_error('Type not implemented yet');
                        exit;
                    }
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
     * @deprecated this is specific to the page and thus, should be in the page mapper
     * note: this is already in the page mapper, it has to be eventually removed from here
     */
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();
        $collection->getPage()->setIsMapped(true);
        $file = $this->registry->getPageMapper()->getFile($collection->getPage(), $this->registry->getLanguage()->getLanguage());
        $data = $this->registry->getStorage()->loadBranchData($file);

        if (is_array($data) && !$variables->isMapped()) {
            $this->mapJsonCollection($variables, $data);
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
                    if ($variable instanceof VariableEntity) {
                        $this->mapJsonVariable($variable, $data);
                    } elseif ($variable instanceof VariableImage) {
                        $this->mapJsonImage($variable, $data);
                    } elseif ($variable instanceof VariableGroup) {
                        $this->mapJsonGroup($variable, $data);
                    } else {
                        trigger_error('Need to impleement this type toooo');
                        exit;
                    }
                }
            }
        }
        $collection->setIsMapped(true);
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
                } elseif ($variable instanceof VariableImage) {
                    $this->mapJsonImage($variable, $json['variables'][$variable->getName()]);
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
     * @return VariableArrayItem
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
                if ($variable instanceof VariableEntity) {
                    $this->mapJsonVariable($variable, $data[$variable->getName()]);
                } elseif ($variable instanceof VariableArray) {
                    $this->mapJsonArray($variable, $data[$variable->getName()]);
                } elseif ($variable instanceof VariableImage) {
                    $this->mapJsonImage($variable, $data[$variable->getName()]);
                }
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
        $variable->setValue(isset($json['value']) ? $json['value'] : null);
        return $variable;
    }

    /**
     * @param VariableImage $variable
     * @param array $json
     * @return VariableImage
     */
    public function mapJsonImage(VariableImage $variable, array $json)
    {
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
        return $variable;
    }
}