<?php

namespace Shopware\Connect\Service;

use Phake;
use Shopware\Connect\Gateway\ReservationGateway;
use Shopware\Connect\Gateway\ShopConfiguration;
use Shopware\Connect\Logger;
use Shopware\Connect\ProductFromShop;
use Shopware\Connect\Struct;

class TransactionServiceTest extends \PHPUnit_Framework_TestCase
{
    const BUYER_SHOP_ID = 1;
    const APIKEY = 1234;
    const OFFER_VALID_UNTIL = 2067855023; // 12.07.2035 12:10

    private $fromShop;
    private $gateway;
    private $logger;
    private $configuration;
    /** @var Transaction */
    private $transaction;

    static public function matchingAvailabilityGroups()
    {
        return array(
            array(1, 1), // equal
            array(5, 1), // low group
            array(99, 11), // medium group
            array(201, 101), // high group
            array(1, 1000), // we have MUCH MORE suddenly
        );
    }

    public function setUp()
    {
        $this->fromShop = $this->createMock(ProductFromShop::class);
        $this->gateway = $this->createMock(ReservationGateway::class);
        $this->logger = $this->createMock(Logger::class);
        $this->configuration = $this->createMock(ShopConfiguration::class);
        $this->transaction = new Transaction(
            $this->fromShop,
            $this->gateway,
            $this->logger,
            $this->configuration,
            $this->createMock(Struct\VerificatorDispatcher::class),
            $this->socialNetwork = $this->createMock(SocialNetwork::class),
            self::APIKEY
        );

        $this->fromShop->method('calculateShippingCosts')->with($this->anything())->willReturn(new Struct\Shipping());
    }

    /**
     * @dataProvider matchingAvailabilityGroups
     */
    public function testMatchingAvailabilityGroups($remoteAvailability, $actualAvailability)
    {
        $remoteProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => $remoteAvailability,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));
        $localProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => $actualAvailability,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);
        self::assertProvidesShippingCosts($result);
    }

    private function assertProvidesShippingCosts(Struct\CheckResult $result)
    {
        self::assertCount(1, $result->shippingCosts);
        self::assertContainsOnly('Shopware\Connect\Struct\Shipping', $result->shippingCosts);

        self::assertInstanceOf('Shopware\Connect\Struct\Shipping', $result->aggregatedShippingCosts);
    }

    public function testNonMatchingAvailabilityGroups()
    {
        $remoteProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));
        $localProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 50,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);

        self::assertContainsOnly('Shopware\Connect\Struct\Change\InterShop\Unavailable', $result->changes);
    }

    public function testInvalidPriceHash()
    {
        $remoteProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(10, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));
        $localProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);

        self::assertContainsOnly('Shopware\Connect\Struct\Change\InterShop\Update', $result->changes);
    }

    public function testChangedFixedPrice()
    {
        $remoteProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 10,
            'fixedPrice' => true,
            'price' => 10,
            'purchasePriceHash' => PurchasePriceSecurity::hash(10, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));
        $localProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 10,
            'fixedPrice' => true,
            'price' => 11,
            'purchasePriceHash' => PurchasePriceSecurity::hash(10, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);

        self::assertContainsOnly('Shopware\Connect\Struct\Change\InterShop\Update', $result->changes);
    }

    public function testNegativeAvailabillity()
    {
        $remoteProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 1,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));
        $localProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => -1,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);

        self::assertContainsOnly('Shopware\Connect\Struct\Change\InterShop\Unavailable', $result->changes);
    }

    public function testCheckProductsWithConnectShippingRules()
    {
        $product = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 1,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $this->configuration->method('getShopConfiguration')->with($this->anything())->willReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'all')));

        $result = $this->whenCheckProductsWith($product, $product);

        self::assertTrue($result->aggregatedShippingCosts->isShippable);
        self::assertEquals(0, $result->aggregatedShippingCosts->shippingCosts);
        self::assertEquals(0, $result->aggregatedShippingCosts->grossShippingCosts);
    }

    public function testCheckProductsWithConnectShippingRules_NotshippableToGermanIslands()
    {
        $product = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 1,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $this->configuration->method('getShopConfiguration')->with($this->anything())->willReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'all')));

        $result = $this->whenCheckProductsWith($product, $product, 'DEU', 18565);

        self::assertFalse($result->aggregatedShippingCosts->isShippable);
    }

    public function testCheckIncludesPriceGroupMarginOnPurchasePrice()
    {
        $remoteProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 90,
            'purchasePriceHash' => PurchasePriceSecurity::hash(90, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));
        $localProduct = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 100,
            'purchasePrice' => 100,
            'purchasePriceHash' => PurchasePriceSecurity::hash(100, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);

        self::assertProvidesShippingCosts($result);
    }

    private function whenCheckProductsWith($localProduct, $remoteProduct, $country = 'DEU', $zipCode = 53225)
    {
        $products = new Struct\Order(array(
            'products' => array(new Struct\OrderItem(array(
                'product' => $remoteProduct,
            ))),
            'deliveryAddress' => new Struct\Address(array(
                'country' => $country,
                'zip' => $zipCode,
            ))
        ));

        $this->configuration->method('getShopConfiguration')->with($this->anything())->willReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'remote')));
        $this->fromShop->method('getProducts')->with(array(10))->willReturn(array($localProduct));

        return $this->transaction->checkProducts($products, self::BUYER_SHOP_ID);
    }

    public function testBuyWithConnectShippingRules_AsksConnectRestApi()
    {
        $this->configuration->method('getShopConfiguration')->with($this->anything())->willReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'all')));
        $this->gateway->method('getOrder')->with(1234)->willReturn($order = new Struct\Order());
        $this->socialNetwork->method('calculateShippingCosts')->with($order)->willReturn(new Struct\Shipping());

        $this->logger->expects($this->atLeastOnce())->method('log')->with($order);

        $return = $this->transaction->buy(1234, 5678);

        self::assertNotInstanceOf(Struct\Error::class, $return, $return ? $return->debugText : '');

        self::assertInstanceOf(Struct\Shipping::class, $order->shipping);
    }

    public function testCheckProductsWithProductNotInStock()
    {
        $product = new Struct\Product(array(
            'sourceId' => 10,
            'availability' => 0,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $this->configuration->method('isFeatureEnabled')->with('sellNotInStock')->willReturn(true);

        $result = $this->whenCheckProductsWith($product, $product, 'DEU', 18565);

        self::assertEmpty($result->changes);
    }
}
