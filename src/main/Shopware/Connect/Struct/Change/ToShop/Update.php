<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct\Change\ToShop;

/**
 * Represents a change in purchase price.
 */
class Update extends ToShopChange
{
    /**
     * @var \Shopware\Connect\Struct\ProductUpdate
     */
    public $product;
}
