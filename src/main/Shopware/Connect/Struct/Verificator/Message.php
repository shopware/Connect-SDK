<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\Struct\Verificator;
use Shopware\Connect\Struct\VerificatorDispatcher;
use Shopware\Connect\Struct;

/**
 * Visitor verifying integrity of struct classes
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Message extends Verificator
{
    /**
     * Method to verify a structs integrity
     *
     * Throws a RuntimeException if the struct does not verify.
     *
     * @param VerificatorDispatcher $dispatcher
     * @param Struct $struct
     * @return void
     */
    protected function verifyDefault(VerificatorDispatcher $dispatcher, Struct $struct)
    {
        if (!is_string($struct->message)) {
            throw new \Shopware\Connect\Exception\VerificationFailedException('$message MUST be a string.');
        }

        if (!is_array($struct->values)) {
            throw new \Shopware\Connect\Exception\VerificationFailedException('$values MUST be an array.');
        }
    }
}
