<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\ShippingCostCalculator\Aggregator;

use Shopware\Connect\ShippingCostCalculator\Aggregator;
use Shopware\Connect\Struct\Shipping;

class Sum extends Aggregator
{
    /**
     * Aggregate shipping costs
     *
     * Aggregate shipping costs of order items and return the sum of all
     * shipping costs.
     *
     * Optionally provide return object.
     *
     * @param Shipping[] $shippings
     * @param Shipping $shipping
     * @return Shipping
     */
    public function aggregateShippingCosts(array $shippings, Shipping $shipping = null)
    {
        return array_reduce(
            $shippings,
            function (Shipping $shipping, Shipping $next) {
                $shipping->isShippable = $shipping->isShippable && $next->isShippable;
                $shipping->shippingCosts += $next->shippingCosts;
                $shipping->grossShippingCosts += $next->grossShippingCosts;
                $shipping->deliveryWorkDays = max(
                    $shipping->deliveryWorkDays,
                    $next->deliveryWorkDays
                );

                $shipping->service = implode(', ', array_filter(array_unique(array_merge(explode(', ', $shipping->service), [$next->service]))));

                return $shipping;
            },
            $shipping ?: new Shipping()
        );
    }
}
