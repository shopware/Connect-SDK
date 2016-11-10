<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Gateway;

/**
 * Test for the in mysqli gateway implementation.
 */
class MySQLiChangeGatewayTest extends ChangeGatewayTest
{
    /**
     * Factory method which creates our concrete SUT instance.
     *
     * @return \Shopware\Connect\Gateway\ChangeGateway
     */
    protected function createChangeGateway()
    {
        $config = @parse_ini_file(__DIR__ . '/../../../../build.properties');
        $connection = new \Shopware\Connect\MySQLi(
            $config['db.hostname'],
            $config['db.userid'],
            $config['db.password'],
            $config['db.name']
        );
        $connection->query('TRUNCATE TABLE sw_connect_change;');
        $connection->query('TRUNCATE TABLE sw_connect_product;');

        return new MySQLi($connection);
    }
}
