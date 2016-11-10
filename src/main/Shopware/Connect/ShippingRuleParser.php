<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

abstract class ShippingRuleParser
{
    /**
     * Parse shipping rules out of string
     *
     * @param string $string
     * @return Struct\ShippingRules
     */
    abstract public function parseString($string);
}
