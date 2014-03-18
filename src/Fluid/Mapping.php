<?php
namespace Fluid;

use SimpleXMLElement;

class Mapping implements MappingInterface
{
    /**
     * @var SimpleXMLElement
     */
    private $xmlElement;

    /**
     * @param SimpleXMLElement|null $xmlElement
     */
    public function __construct(SimpleXMLElement $xmlElement = null)
    {
        $this->setXmlElement($xmlElement);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $xmlElement = $this->getXmlElement();

        if (isset($xmlElement->config)) {
            /** @var SimpleXMLElement $element */
            $element = $xmlElement->config;
            /** @var SimpleXMLElement $setting */
            foreach ($element->children() as $setting) {
                $settingKey = null;
                foreach ($setting->attributes() as $key => $value) {
                    $value = (string)$value;
                    if ($key === 'name') {
                        $settingKey = $value;
                    } else if ($settingKey !== null && $key === 'value') {
                        $config[$settingKey] = $value;
                        $settingKey = null;
                    }
                }
            }
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        if ($this->getXmlElement() instanceof SimpleXMLElement) {
            return $this->getChilds($this->getXmlElement());
        }
        return [];
    }

    /**
     * @param SimpleXMLElement $element
     * @return array
     */
    protected function getChilds(SimpleXMLElement $element)
    {
        $count = 0;
        $childs = [];
        /** @var SimpleXMLElement $child) */
        foreach ($element->children() as $child) {
            $item = [];
            if ($child->count()) {
                $item = $this->getChilds($child);
            }
            $item['attributes'] = [];
            foreach ($child->attributes() as $key => $value) {
                $item['attributes'][(string)$key] = (string)$value;
            }

            if (!isset($childs[$count])) {
                $childs[$count] = [];
            }
            $item['name'] = $child->getName();
            $childs[$count] = $item;
            $count++;

        }
        return $childs;
    }

    /**
     * @param SimpleXMLElement|null $xmlElement
     * @return $this
     */
    public function setXmlElement(SimpleXMLElement $xmlElement = null)
    {
        $this->xmlElement = $xmlElement;
        return $this;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getXmlElement()
    {
        return $this->xmlElement;
    }
}