<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct;

use Shopware\Connect\Struct;

class CheckResult extends Struct
{
    /**
     * @var Struct\Change[]
     */
    public $changes = [];

    /**
     * Errors
     *
     * @var Message[]
     */
    public $errors = [];

    /**
     * @var Shipping[]
     */
    public $shippingCosts = [];

    /**
     * @var Shipping
     */
    public $aggregatedShippingCosts;

    /**
     * Has errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (bool) count($this->errors);
    }
}
