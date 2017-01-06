<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct;

use Shopware\Connect\Struct;

/**
 * Struct class, representing property information for a product
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 * @api
 */
class Property extends Struct
{
    /**
     * @var string
     */
    public $groupName;

    /**
     * @var boolean
     */
    public $comparable;

    /**
     * @var int
     */
    public $sortMode;

    /**
     * @var string
     */
    public $option;

    /**
     * @var boolean
     */
    public $filterable;

    /**
     * @var string
     */
    public $value;

}