<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\Struct\Order;
use Shopware\Connect\Struct\Shipping;
use Shopware\Connect\ShippingCosts\VatConfig;

/**
 * Rule decorator, which applies the delegatee only if a given basket value is
 * reached.
 */
class StaggerPrice extends Rule
{
    /**
     * @var string
     */
    public $label;

    /**
     * Delivery work days
     *
     * @var int
     */
    public $deliveryWorkDays = 10;

    /**
     * Default shipping costs, if no stagger price matches
     *
     * @var float
     */
    public $default = 0;

    /**
     * Array defining the stagger prices
     *
     * Structure of the array:
     *
     * <code>
     *  array(
     *      '<minimumValue>' => '<costs>',
     *      '<minimumValue>' => '<costs>',
     *      …
     *  )
     * </code>
     *
     * The highest matching minimumValue will define the shipping costs used
     * for the current order.
     *
     * @var array
     */
    public $staggerPrices = [];

    /**
     * Check if shipping cost is applicable to given order
     *
     * @param Order $order
     * @return bool
     */
    public function isApplicable(Order $order)
    {
        return true;
    }

    /**
     * Get shipping costs for order
     *
     * Returns the net shipping costs.
     *
     * @param Order $order
     * @param VatConfig $vatConfig
     * @return Shipping
     */
    public function getShippingCosts(Order $order, VatConfig $vatConfig)
    {
        $prices = $this->staggerPrices + [0 => $this->default];
        krsort($prices, SORT_NUMERIC);

        $total = 0;
        foreach ($order->orderItems as $item) {
            $total += ($item->count * $item->product->purchasePrice * (1 + $item->product->vat));
        }

        foreach ($prices as $value => $costs) {
            if ($total > $value) {
                break;
            }
        }

        return new Shipping(
            [
                'rule' => $this,
                'service' => $this->label,
                'deliveryWorkDays' => $this->deliveryWorkDays,
                'shippingCosts' => $costs / ($vatConfig->isNet ? 1 : 1 + $vatConfig->vat),
                'grossShippingCosts' => $costs * (!$vatConfig->isNet ? 1 : 1 + $vatConfig->vat),
            ]
        );
    }
}
