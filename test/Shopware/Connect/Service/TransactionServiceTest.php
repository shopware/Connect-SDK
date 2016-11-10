<?php

namespace Shopware\Connect\Service;

use Phake;
use Shopware\Connect\Struct;

class TransactionServiceTest extends \PHPUnit_Framework_TestCase
{
    const BUYER_SHOP_ID = 1;
    const APIKEY = 1234;
    const OFFER_VALID_UNTIL = 2067855023; // 12.07.2035 12:10

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
        $this->fromShop = Phake::mock('Shopware\Connect\ProductFromShop');
        $this->gateway = Phake::mock('Shopware\Connect\Gateway\ReservationGateway');
        $this->logger = Phake::mock('Shopware\Connect\Logger');
        $this->configuration = Phake::mock('Shopware\Connect\Gateway\ShopConfiguration');
        $this->transaction = new Transaction(
            $this->fromShop,
            $this->gateway,
            $this->logger,
            $this->configuration,
            \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher'),
            $this->socialNetwork = \Phake::mock('Shopware\Connect\Service\SocialNetwork'),
            self::APIKEY
        );

        Phake::when($this->fromShop)->calculateShippingCosts(Phake::anyParameters())->thenReturn(new Struct\Shipping());
        Phake::when($this->configuration)->getShopConfiguration(Phake::anyParameters())->thenReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'remote')));
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
        $this->assertProvidesShippingCosts($result);
    }

    private function assertProvidesShippingCosts(Struct\CheckResult $result)
    {
        $this->assertCount(1, $result->shippingCosts);
        $this->assertContainsOnly('Shopware\Connect\Struct\Shipping', $result->shippingCosts);

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result->aggregatedShippingCosts);
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
            'availability' => 0,
            'purchasePrice' => 5,
            'purchasePriceHash' => PurchasePriceSecurity::hash(5, self::OFFER_VALID_UNTIL, self::APIKEY),
            'offerValidUntil' => self::OFFER_VALID_UNTIL,
        ));

        $result = $this->whenCheckProductsWith($localProduct, $remoteProduct);

        $this->assertContainsOnly('Shopware\Connect\Struct\Change\InterShop\Unavailable', $result->changes);
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

        $this->assertContainsOnly('Shopware\Connect\Struct\Change\InterShop\Unavailable', $result->changes);
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

        Phake::when($this->configuration)->getShopConfiguration(Phake::anyParameters())->thenReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'all')));

        $result = $this->whenCheckProductsWith($product, $product);

        $this->assertTrue($result->aggregatedShippingCosts->isShippable);
        $this->assertEquals(0, $result->aggregatedShippingCosts->shippingCosts);
        $this->assertEquals(0, $result->aggregatedShippingCosts->grossShippingCosts);
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

        Phake::when($this->configuration)->getShopConfiguration(Phake::anyParameters())->thenReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'all')));

        $result = $this->whenCheckProductsWith($product, $product, 'DEU', 18565);

        $this->assertFalse($result->aggregatedShippingCosts->isShippable);
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

        $this->assertProvidesShippingCosts($result);
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

        \Phake::when($this->fromShop)->getProducts(array(10))->thenReturn(array($localProduct));

        return $this->transaction->checkProducts($products, self::BUYER_SHOP_ID);
    }

    public function testBuyWithConnectShippingRules_AsksConnectRestApi()
    {
        Phake::when($this->configuration)->getShopConfiguration(Phake::anyParameters())->thenReturn(new Struct\ShopConfiguration(array('shippingCostType' => 'all')));
        Phake::when($this->gateway)->getOrder(1234)->thenReturn($order = new Struct\Order());
        Phake::when($this->socialNetwork)->calculateShippingCosts($order)->thenReturn(new Struct\Shipping());

        $return = $this->transaction->buy(1234, 5678);

        $this->assertNotInstanceOf('Shopware\Connect\Struct\Error', $return, $return ? $return->debugText : '');

        \Phake::verify($this->logger)->log($order);

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $order->shipping);
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

        Phake::when($this->configuration)->isFeatureEnabled('sellNotInStock')->thenReturn(true);

        $result = $this->whenCheckProductsWith($product, $product, 'DEU', 18565);

        $this->assertEmpty($result->changes);
    }
}

