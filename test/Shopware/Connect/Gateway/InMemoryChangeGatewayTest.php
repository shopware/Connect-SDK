<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Gateway;

/**
 * Test for the in memory change gateway implementation.
 */
class InMemoryChangeGatewayTest extends ChangeGatewayTest
{
    /**
     * Factory method which creates our concrete SUT instance.
     *
     * @return \Shopware\Connect\Gateway\ChangeGateway
     */
    protected function createChangeGateway()
    {
        return new InMemory();
    }
}
