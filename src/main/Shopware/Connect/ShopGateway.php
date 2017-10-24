<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

/**
 * Shop gateway base class
 *
 * Gateway to interact with other shops
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
abstract class ShopGateway
{
    /**
     * Check order in shop
     *
     * Verifies, if all products in the given list still have the same price
     * and availability as in the remote shop..
     *
     * Returns true on success, or an array of Struct\Change with updates for
     * the requested products.
     *
     * @param Struct\Order $order
     * @param string $shopId
     * @return Struct\CheckResult
     */
    abstract public function checkProducts(Struct\Order $order, $shopId);

    /**
     * Reserve order in shop
     *
     * Products SHOULD be reserved and not be sold out while bing reserved.
     * Reservation may be cancelled after sufficient time has passed.
     *
     * Returns a reservationId on success, or an array of Struct\Change with
     * updates for the requested products.
     *
     * @param Struct\Order $order
     * @return mixed
     */
    abstract public function reserveProducts(Struct\Order $order);

    /**
     * Buy order associated with reservation in the remote shop.
     *
     * Returns true on success, or a Struct\Message on failure. SHOULD never
     * fail.
     *
     * @param string $reservationId
     * @param string $orderId
     * @return mixed
     */
    abstract public function buy($reservationId, $orderId);

    /**
     * Confirm a reservation in the remote shop.
     *
     * Returns true on success, or a Struct\Message on failure. SHOULD never
     * fail.
     *
     * @param string $reservationId
     * @param string $remoteLogTransactionId
     * @return mixed
     */
    abstract public function confirm($reservationId, $remoteLogTransactionId);
}
