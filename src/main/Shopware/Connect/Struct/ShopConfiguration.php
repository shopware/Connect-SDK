<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct;

use Shopware\Connect\Struct;

/**
 * Struct class for shop configurations
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 * @api
 */
class ShopConfiguration extends Struct
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $serviceEndpoint;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $key;

    /**
     * Defines how the shipping costs are calculated for merchant and consumer.
     * This field shows to supplier how shipping costs should be calculated.
     *
     * @var string
     */
    public $shippingCostType = 'remote';

    /**
     * Defines how the shipping costs are calculated when an account
     * makes orders with products from current supplier.
     * This field shows to merchant how shipping costs will be calculated for imported products in his shop.
     *
     * @var string
     */
    public $importedProductsShippingCostType = 'remote';

    /**
     * @var string
     */
    public $logoUrl;

    /**
     * @var bool
     */
    public $sellNotInStock;

    /**
     * Restores a shop configuration from a previously stored state array.
     *
     * @param array $state
     * @return \Shopware\Connect\Struct\ShopConfiguration
     */
    public static function __set_state(array $state)
    {
        unset($state['priceGroupMargin']);
        unset($state['shippingCost']);

        return new self($state);
    }
}
