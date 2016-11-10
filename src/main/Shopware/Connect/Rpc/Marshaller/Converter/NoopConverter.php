<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Rpc\Marshaller\Converter;

use Shopware\Connect\Rpc\Marshaller\Converter;

class NoopConverter extends Converter
{
    /**
     * Convert the given php object of arbitrary type to another one and return it.
     *
     * There are two possible scenarios, when this will affect the process:
     *
     * Marshalling:
     *   - The returned object will be marshalled instead of the original one.
     *
     * Unmarshalling:
     *   - The original object has been unmarshalled, but instead of it the returned object will be
     *     provided by the unmarshaller to its caller
     *
     * @param mixed $object
     * @return mixed
     */
    public function convertObject($object)
    {
        // The NoOperation converter simply does no conversion at all
        return $object;
    }
}
