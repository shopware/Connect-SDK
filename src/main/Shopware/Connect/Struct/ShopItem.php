<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct;

use Shopware\Connect\Struct;

/**
 * Struct class with additional internal properties for shop items
 *
 * All properties in this class are internal to the SDK. Users should not care.
 * Properties will be overwritten by the SDK anyways.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
abstract class ShopItem extends Struct
{
    /**
     * ID of the shop.
     *
     * Will be set by the SDK.
     *
     * @var string
     * @access private
     */
    public $shopId;

    /**
     * Product revision
     *
     * Will be set by the SDK.
     *
     * @var string
     * @access private
     */
    public $revisionId;
}
