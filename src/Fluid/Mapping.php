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
     * @param SimpleXMLElement $xmlElement
     */
    public function __construct(SimpleXMLElement $xmlElement)
    {
        $this->setXmlElement($xmlElement);
    }

    /**
     * @param SimpleXMLElement $xmlElement
     * @return $this
     */
    public function setXmlElement(SimpleXMLElement $xmlElement)
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