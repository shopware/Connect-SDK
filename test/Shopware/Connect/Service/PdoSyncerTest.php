<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Service;

require_once __DIR__ . '/SyncerTest.php';

class PdoSyncerTest extends SyncerTest
{
    protected $gateway;

    /**
     * Get used gateway for test
     *
     * @return SDK\Gateway
     */
    protected function getGateway()
    {
        if ($this->gateway) {
            return $this->gateway;
        }

        $config = @parse_ini_file(__DIR__ . '/../../../../build.properties');
        $dsn = sprintf(
            'mysql:dbname=%s;host=%s',
            $config['db.name'],
            $config['db.hostname']
        );
        $connection = new \PDO(
            $dsn,
            $config['db.userid'],
            $config['db.password']
        );
        $connection->query('TRUNCATE TABLE sw_connect_change;');
        $connection->query('TRUNCATE TABLE sw_connect_product;');
        $connection->query('TRUNCATE TABLE sw_connect_shop_config;');

        return $this->gateway = new \Shopware\Connect\Gateway\PDO($connection);
    }
}
