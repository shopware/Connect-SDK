<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\Gateway;
use Shopware\Connect\SDK;
use Shopware\Connect\Struct;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testReplicatePriceType()
    {
        $gateway = new Gateway\InMemory();
        $service = new Configuration($gateway);

        $service->replicate(array(
            array(
                'configuration' => new Struct\Configuration(array(
                    'priceType' => SDK::PRICE_TYPE_PURCHASE,
                    'billingAddress' => new Struct\Address()
                ))
            )
        ));

        $this->assertEquals(SDK::PRICE_TYPE_PURCHASE, $gateway->getConfig(SDK::CONFIG_PRICE_TYPE));
    }

    public function testReplicatePriceTypeUpdate()
    {
        $gateway = new Gateway\InMemory();
        $service = new Configuration($gateway);
        $gateway->setConfig(SDK::CONFIG_PRICE_TYPE, SDK::PRICE_TYPE_RETAIL);

        $service->replicate(array(
            array(
                'configuration' => new Struct\Configuration(array(
                    'priceType' => SDK::PRICE_TYPE_PURCHASE,
                    'billingAddress' => new Struct\Address()
                ))
            )
        ));

        $this->assertEquals(SDK::PRICE_TYPE_PURCHASE, $gateway->getConfig(SDK::CONFIG_PRICE_TYPE));
    }

    public function testReplicatePriceTypeChangeNoneType()
    {
        $gateway = new Gateway\InMemory();
        $service = new Configuration($gateway);

        $gateway->setConfig(SDK::CONFIG_PRICE_TYPE, SDK::PRICE_TYPE_NONE);

        $service->replicate(array(
            array(
                'configuration' => new Struct\Configuration(array(
                    'priceType' => SDK::PRICE_TYPE_PURCHASE,
                    'billingAddress' => new Struct\Address()
                ))
            )
        ));

        $this->assertEquals(SDK::PRICE_TYPE_PURCHASE, $gateway->getConfig(SDK::CONFIG_PRICE_TYPE));
    }
}
