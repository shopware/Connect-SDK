<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct\Metric;

use \Shopware\Connect\Struct\Metric;

/**
 * Count metric
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Count extends Metric
{
    /**
     * Count value
     *
     * @var int
     */
    public $count;

    public function __toString()
    {
        return sprintf(
            " METRIC_COUNT metric=%s value=%d ",
            $this->name,
            $this->count
        );
    }
}
