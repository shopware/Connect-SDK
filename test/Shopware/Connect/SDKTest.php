<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

use Shopware\Connect\Struct\ShopConfiguration;

class SDKTest extends \PHPUnit_Framework_TestCase
{
    private $sdk;

    private $gatewayMock;

    private $productToShopMock;

    private $productFromShopMock;

    public function setUp()
    {
        $this->gatewayMock = $this->createMock(Gateway::class);
        $this->productToShopMock = $this->createMock(ProductToShop::class);
        $this->productFromShopMock = $this->createMock(ProductFromShop::class);

        $this->sdk = new SDK(
            'apiKey',
            'http://example.com/api',
            $this->gatewayMock,
            $this->productToShopMock,
            $this->productFromShopMock,
            null,
            new HttpClient\NoSecurityRequestSigner()
        );
    }

    public function testGetShop()
    {
        $shopId = 1234;
        $shopConfig = new ShopConfiguration([
            'displayName' => 'Test-Shop',
            'url' => 'http://foo',
        ]);

        $this->gatewayMock
            ->expects($this->once())
            ->method('getShopConfiguration')
            ->with($this->equalTo($shopId))
            ->will($this->returnValue($shopConfig));

        $shop = $this->sdk->getShop($shopId);

        $this->assertInstanceOf('Shopware\Connect\Struct\Shop', $shop);
        $this->assertEquals('Test-Shop', $shop->name);
        $this->assertEquals($shopId, $shop->id);
        $this->assertEquals('http://foo', $shop->url);
    }

    public function testPingRequest()
    {
        $responseBody = $this->sdk->handle('', ['HTTP_X_SHOPWARE_CONNECT_PING' => '']);

        $this->assertEquals(
            '<?xml version="1.0" encoding="utf-8"?>' . "\n"
                . '<pong/>',
            $responseBody
        );
    }

    public function testIsPriceTypeValid()
    {
        //valid PriceTypes
        $this->assertEquals(true, $this->sdk->isPriceTypeValid(SDK::PRICE_TYPE_PURCHASE));
        $this->assertEquals(true, $this->sdk->isPriceTypeValid(SDK::PRICE_TYPE_RETAIL));
        $this->assertEquals(true, $this->sdk->isPriceTypeValid(SDK::PRICE_TYPE_BOTH));

        //invalid
        $this->assertEquals(false, $this->sdk->isPriceTypeValid(SDK::PRICE_TYPE_NONE));
        $this->assertEquals(false, $this->sdk->isPriceTypeValid(42));
        $this->assertEquals(false, $this->sdk->isPriceTypeValid(-2));
    }
}
