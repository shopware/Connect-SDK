<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct\Change\ToShop;

use Shopware\Connect\Struct\Product;

/**
 * Insert change struct
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 * @api
 */
class InsertOrUpdate extends ToShopChange
{
    /**
     * Product, which is inserted or updated
     *
     * @var Product
     */
    public $product;

    public function __set($name, $value)
    {
        //in legacy updater code it is called with ShopProduct struct -> this converts it to the new Product Struct
        if ($name === 'product' && (!$value instanceof Product)) {
            $this->product = new Product();
            foreach ($value->toArray() as $name => $value) {
                try {
                    $this->product->$name = $value;
                } catch (\Exception $e) {
                    //catch everything try to convert legacy ShopProduct to new Product struct
                }
            }
        } else {
            parent::__set($name, $value);
        }
    }
}
