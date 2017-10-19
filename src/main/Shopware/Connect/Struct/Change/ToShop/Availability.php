<?php

namespace Shopware\Connect\Struct\Change\ToShop;

/**
 * Availability of Product has changed in FromShop.
 */
class Availability extends ToShopChange
{
    /**
     * New availability for product
     *
     * @var int
     */
    public $availability;
}
