<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Logger;

use Bepado\Common;
use Shopware\Connect\Struct;
use Shopware\Connect\HttpClient;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    const APIKEY = '1234';

    /**
     * Get a valid order struct
     *
     * @return Struct\Order
     */
    protected function getValidOrder()
    {
        return new Struct\Order(array(
            'orderShop' => 'shop1',
            'providerShop' => 'shop2',
            'reservationId' => md5(microtime()),
            'localOrderId' => md5(microtime()),
            'providerOrderId' => md5(microtime()),
            'shippingCosts' => 34.43,
            'products' => array(
                new Struct\OrderItem(array(
                    'count' => 2,
                    'product' => array(
                        new Struct\Product(array(
                            'shopId' => 'shop1',
                            'sourceId' => '1-23',
                            'title' => 'Sindelfingen',
                            'price' => 42.23,
                            'currency' => 'EUR',
                            'availability' => 5,
                        ))
                    ),
                ))
            ),
            'deliveryAddress' => new Struct\Address(array(
                'name' => 'Hans Mustermann',
                'line1' => 'Musterstrasse 23',
                'zip' => '12345',
                'city' => 'Musterstadt',
                'country' => 'Germany',
            )),
        ));
    }

    public function testLog()
    {
        $order = $this->getValidOrder();
        $logger = new Http(
            $httpClient = $this->createMock(HttpClient::class),
            self::APIKEY
        );

        $httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/transaction',
                json_encode($order)
            )
            ->will(
                $this->returnValue(
                    new HttpClient\Response(
                        array(
                            'status' => 200,
                            'body' => '{"shopId":"shop1"}',
                        )
                    )
                )
            );

        $logger->log($order);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOrder()
    {
        $order = new Struct\Order();
        $logger = new Http(
            $httpClient = $this->createMock(HttpClient::class),
            self::APIKEY
        );

        $logger->log($order);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoggingFailed()
    {
        $order = $this->getValidOrder();
        $logger = new Http(
            $httpClient = $this->createMock(HttpClient::class),
            self::APIKEY
        );

        $httpClient
            ->expects($this->once())
            ->method('request')
            ->will(
                $this->returnValue(
                    new HttpClient\Response(
                        array(
                            'status' => 500,
                        )
                    )
                )
            );

        $logger->log($order);
    }

    public function testAgainstLoggingService()
    {
        $order = $this->getValidOrder();
        $logger = new Http(
            new HttpClient\Stream('http://transaction.bepado.local/'),
            self::APIKEY
        );

        try {
            ini_set('default_socket_timeout', 1);
            return $logger->log($order);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped("Cannot reach real service.");
        }
    }

    /**
     * @depends testAgainstLoggingService
     */
    public function testConfirmAgainstLoggingService($token)
    {
        $logger = new Http(
            new HttpClient\Stream('http://transaction.bepado.local/'),
            self::APIKEY
        );

        $logger->confirm($token);
    }
}
