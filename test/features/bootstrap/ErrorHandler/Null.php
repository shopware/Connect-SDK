<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\ErrorHandler;

use Shopware\Connect\ErrorHandler;
use Shopware\Connect\Struct;

/**
 * Base class for logger implementations
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Null extends ErrorHandler
{
    /**
     * Handle error
     *
     * @param Struct\Error $error
     * @return void
     */
    public function handleError(Struct\Error $error)
    {
        // Don't do nothing
    }

    /**
     * Handle exception
     *
     * @param \Exception $exception
     * @return void
     */
    public function handleException(\Exception $exception)
    {
        // Don't do nothing
    }
}
