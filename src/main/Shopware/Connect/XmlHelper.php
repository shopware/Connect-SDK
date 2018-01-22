<?php
/**
 * This file is part of the Shopware Connect SDK component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

/**
 * Utility class for xml handling.
 */
class XmlHelper
{
    /**
     * @param \DOMElement $element
     * @param string $urn
     *
     * @return void
     */
    public function updateDefaultNamespace(\DOMElement $element, $urn)
    {
        $element->setAttribute(
            'xmlns',
            $urn
        );
    }

    /**
     * @param \DOMElement $element
     * @param string $urn
     * @param string $prefix
     *
     * @return void
     */
    public function updateRelativeNamespacePrefix(\DOMElement $element, $urn, $prefix)
    {
        $element->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            "xmlns:{$prefix}",
            $urn
        );
    }

    /**
     * @param \DOMElement $element
     * @param array $urnLocationMapping
     *
     * @return void
     */
    public function updateSchemaLocation(\DOMElement $element, array $urnLocationMapping)
    {
        $attributeValues = [];
        foreach ($urnLocationMapping as $urn => $location) {
            $attributeValues[] = $urn;
            $attributeValues[] = $location;
        }

        $element->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            implode(' ', $attributeValues)
        );
    }
}
