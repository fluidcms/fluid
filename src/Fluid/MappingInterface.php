<?php
namespace Fluid;

use SimpleXMLElement;

interface MappingInterface
{
    /**
     * @param SimpleXMLElement $xmlElement
     */
    public function __construct(SimpleXMLElement $xmlElement);
}