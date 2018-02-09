<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

/**
 * Base class for logger implementations
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
abstract class Logger
{
    /**
     * Log order
     *
     * @param Struct\Order $order
     * @return void
     */
    public function log(Struct\Order $order)
    {
        foreach (['orderShop', 'providerShop', 'reservationId'] as $property) {
            if (!isset($order->$property)) {
                throw new \InvalidArgumentException("Required order property \$$property not set.");
            }
        }

        return $this->doLog($order);
    }

    /**
     * Log order
     *
     * @param Struct\Order $order
     * @return void
     */
    abstract protected function doLog(Struct\Order $order);

    /**
     * Confirm logging
     *
     * @param string $logTransactionId
     * @return void
     */
    abstract public function confirm($logTransactionId);
}
