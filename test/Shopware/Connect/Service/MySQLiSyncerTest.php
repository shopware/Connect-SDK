<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Service;

use Bepado\Common;
use Shopware\Connect;

require_once __DIR__ . '/SyncerTest.php';

class MySQLiSyncerTest extends SyncerTest
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
        $connection = new \Shopware\Connect\MySQLi(
            $config['db.hostname'],
            $config['db.userid'],
            $config['db.password'],
            $config['db.name']
        );
        $connection->query('TRUNCATE TABLE sw_connect_change;');
        $connection->query('TRUNCATE TABLE sw_connect_product;');

        return $this->gateway = new Connect\Gateway\MySQLi($connection);
    }
}
