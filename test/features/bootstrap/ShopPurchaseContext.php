<?php

namespace Shopware\Connect;

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Shopware\Connect\Struct;
use Shopware\Connect\Controller;
use Shopware\Connect\ShippingCostCalculator;
use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\ShippingCosts\Rules;
use Shopware\Connect\ErrorHandler;
use Shopware\Connect\RPC;

use \PHPUnit_Framework_Assert as Assertion;

require_once __DIR__ . '/SDKContext.php';

/**
 * Features context.
 */
class ShopPurchaseContext extends SDKContext
{
    /**
     * Currently processed order
     *
     * @var Struct\Order
     */
    protected $order;

    /**
     * @var bool
     */
    protected $fixedPriceItems = true;

    /**
     * Result of checkProducts
     *
     * @var Error[]|Order
     */
    protected $checkResult;

    /**
     * Result of reserveProducts
     *
     * @var Reservation
     */
    protected $reserveResult;

    /**
     * Result of checkout
     *
     * @var bool[]
     */
    protected $checkoutResult;

    /**
     * Currently used mock for logger
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Gateway of the remote SDK
     *
     * @var remoteGateway
     */
    protected $remoteGateway;

    protected $reservationId;

    public function initSDK($connection)
    {
        $this->productToShop = \Phake::mock('\\Shopware\\Connect\\ProductToShop');
        $this->productFromShop = \Phake::mock('\\Shopware\\Connect\\ProductFromShop');

        \Phake::when($this->productFromShop)
            ->calculateShippingCosts(\Phake::anyParameters())
            ->thenReturn(
                new Struct\Shipping()
            );

        $this->sdk = new SDK(
            'apikey',
            'http://example.com/endpoint',
            $this->gateway = $this->getGateway($connection),
            $this->productToShop,
            $this->productFromShop,
            null,
            new \Shopware\Connect\HttpClient\NoSecurityRequestSigner()
        );

        $dependenciesProperty = new \ReflectionProperty($this->sdk, 'dependencies');
        $dependenciesProperty->setAccessible(true);
        $this->dependencies = $dependenciesProperty->getValue($this->sdk);

        $this->logger = new Logger\Test();

        $shoppingServiceProperty = new \ReflectionProperty($this->dependencies, 'shoppingService');
        $shoppingServiceProperty->setAccessible(true);
        $shoppingServiceProperty->setValue(
            $this->dependencies,
            new Service\Shopping(
                new ShopFactory\DirectAccess(
                    $this->productToShop,
                    $this->productFromShop,
                    $this->remoteGateway = $this->getGateway($connection),
                    $this->logger
                ),
                new ChangeVisitor\Message(
                    $this->dependencies->getVerificator()
                ),
                $this->productToShop,
                $this->logger,
                new ErrorHandler\Null(),
                $this->gateway
            )
        );

        // Inject custom logger
        $loggerProperty = new \ReflectionProperty($this->dependencies, 'logger');
        $loggerProperty->setAccessible(true);
        $loggerProperty->setValue(
            $this->dependencies,
            $this->logger
        );

        $this->distributeShopConfiguration();
    }

    private function distributeShopConfiguration()
    {
        $billingAddress = new Struct\Address(
            array(
                'name' => 'Shop Doe',
                'line1' => 'Shop-Street 42',
                'zip' => '12345',
                'city' => 'Shopfingen',
                'country' => 'DEU',
                'email' => 'shop@qafoo.com',
                'phone' => '+12345678',
            )
        );

        $this->gateway->setBillingAddress($billingAddress);

        for ($i = 1; $i <= 2; ++$i) {
            $this->remoteGateway->setBillingAddress($billingAddress);

            $this->gateway->setShopConfiguration(
                'shop-' . $i,
                new Struct\ShopConfiguration(
                    array(
                        'serviceEndpoint' => 'http://shop' . $i . '.example.com/',
                        'shippingCostType' => 'remote',
                    )
                )
            );

            $rules = new Rules(array(
                'rules' => array(
                    new Rule\FixedPrice(
                        array(
                            'price' => $i * 2,
                        )
                    )
                )
            ));

            $this->gateway->storeShippingCosts('shop-' . $i, 'shop', "", $rules, $rules);
            $this->remoteGateway->storeShippingCosts('shop-' . $i, 'shop', "", $rules, $rules);
            // for shared state reasons
            $this->gateway->storeShippingCosts('shop-' . $i, 'shop-' . $i, "", $rules, $rules);
            $this->remoteGateway->storeShippingCosts('shop-' . $i, 'shop-' . $i, "", $rules, $rules);
        }

        $this->remoteGateway->setShopConfiguration(
            'shop',
            new Struct\ShopConfiguration(
                array(
                    'serviceEndpoint' => 'http://shop.example.com/',
                    'shippingCostType' => 'remote',
                )
            )
        );
    }

    /**
     * @Given /^The product is listed as available$/
     */
    public function theProductIsListedAsAvailable()
    {
        // Just do nothing…
    }

    /**
     * @Given /^The products? (?:is|are) available in (\d+) shops?$/
     */
    public function theProductIsAvailableInNShops($shops)
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters());

        $products = array();
        for ($i = 1; $i <= $shops; ++$i) {
            $products[] = new Struct\Product(
                array(
                    'shopId' => 'shop-' . $i,
                    'sourceId' => '23-' . $i,
                    'price' => 42.23,
                    'purchasePrice' => 23.42,
                    'purchasePriceHash' => $this->purchasePriceHash(23.42, 'apikey-shop-' . $i),
                    'offerValidUntil' => self::OFFER_VALID_UNTIL,
                    'fixedPrice' => $this->fixedPriceItems,
                    'currency' => 'EUR',
                    'availability' => 5,
                    'title' => 'Sindelfingen',
                    'categories' => array('/others'),
                    'vendor' => 'Foo',
                )
            );
        }

        // this is "wrong" to always return both products of both shops, but
        // the algorithm doesn't mind and the test then works.
        $methodStub->thenReturn($products);
    }

    /**
     * @Given /^A customer adds a product from remote shop (\d+) to basket$/
     */
    public function aCustomerAddsAProductFromARemoteShopToBasket($remoteShop)
    {
        if (!$this->order) {
            $this->order = new Struct\Order();

            $this->order->deliveryAddress = new Struct\Address(
                array(
                    'name' => 'John Doe',
                    'line1' => 'Foo-Street 42',
                    'zip' => '12345',
                    'city' => 'Sindelfingen',
                    'country' => 'DEU',
                    'email' => 'foo@qafoo.com',
                    'phone' => '+12345678',
                )
            );
            $this->order->billingAddress = $this->order->deliveryAddress;
            $this->order->orderShop = 'shop-1';
        }

        $this->order->orderItems[] = new Struct\OrderItem(
            array(
                'count' => 1,
                'product' => new Struct\Product(
                    array(
                        'shopId' => 'shop-' . $remoteShop,
                        'sourceId' => '23-' . $remoteShop,
                        'price' => 42.23,
                        'purchasePrice' => 23.42,
                        'purchasePriceHash' => $this->purchasePriceHash(23.42, 'apikey-shop-' . $remoteShop),
                        'offerValidUntil' => self::OFFER_VALID_UNTIL,
                        'fixedPrice' => $this->fixedPriceItems,
                        'currency' => 'EUR',
                        'availability' => 5,
                        'title' => 'Sindelfingen',
                        'categories' => array('/others'),
                        'vendor' => 'Foo',
                    )
                ),
            )
        );
    }

    /**
     * @When /^The Customer checks out$/
     */
    public function theCustomerChecksOut()
    {
        $this->reserveResult = $this->sdk->reserveProducts($this->order);

        if (current($this->reserveResult->orders)) {
            $this->reservationId = current($this->reserveResult->orders)->reservationId;
        }

        $this->dependencies->getVerificator()->verify($this->reserveResult);

        if ($this->reserveResult->success) {
            $this->checkoutResult = $this->sdk->checkout($this->reserveResult, 'orderId');
        }
    }

    /**
     * @Then /^The customer will receive the products?$/
     */
    public function theCustomerWillReceiveTheProducts()
    {
        foreach ($this->checkoutResult as $shopId => $value) {
            Assertion::assertTrue($value, print_r($this->checkoutResult, true));
        }
    }

    /**
     * @Given /^The product is not available in remote shop$/
     */
    public function theProductIsNotAvailableInRemoteShop()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters())
            ->thenReturn(
                array(
                    new Struct\Product(
                        array(
                            'shopId' => 'shop-1',
                            'sourceId' => '23-1',
                            'price' => 42.23,
                            'purchasePrice' => 23.42,
                            'purchasePriceHash' => $this->purchasePriceHash(23.42, 'apikey-shop-1'),
                            'offerValidUntil' => self::OFFER_VALID_UNTIL,
                            'fixedPrice' => $this->fixedPriceItems,
                            'currency' => 'EUR',
                            'availability' => 0,
                            'title' => 'Sindelfingen',
                            'categories' => array('/others'),
                            'vendor' => 'Foo',
                        )
                    ),
                )
            );
    }

    /**
     * @Given /^The product data is still valid$/
     */
    public function theProductDataIsStillValid()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters())
            ->thenReturn(
                array(
                    new Struct\Product(
                        array(
                            'shopId' => 'shop-1',
                            'sourceId' => '23-1',
                            'price' => 42.23,
                            'purchasePrice' => 23.42,
                            'purchasePriceHash' => $this->purchasePriceHash(23.42, 'apikey-shop-1'),
                            'offerValidUntil' => self::OFFER_VALID_UNTIL,
                            'fixedPrice' => $this->fixedPriceItems,
                            'currency' => 'EUR',
                            'availability' => 5,
                            'title' => 'Sindelfingen',
                            'vendor' => 'Foo',
                            'categories' => array('/others'),
                        )
                    ),
                )
            );
    }

    /**
     * @When /^The Customer views the order overview$/
     */
    public function theCustomerViewsTheOrderOverview()
    {
        $this->checkResult = $this->sdk->checkProducts($this->order);

        if (!$this->checkResult->hasErrors()) {
            $this->reserveResult = $this->sdk->reserveProducts($this->order);
        }
    }

    /**
     * @Then /^The customer is informed about the unavailability$/
     */
    public function theCustomerIsInformedAboutTheUnavailability()
    {
        Assertion::assertEquals(
            array(
                new Struct\Message(
                    array(
                        'message' => 'Availability of product %product changed to %availability.',
                        'values' => array(
                            'product' => '23-1',
                            'availability' => 0,
                        ),
                    )
                )
            ),
            $this->checkResult->errors
        );
    }

    /**
     * @Given /^The product (?:price|availability) is updated in the local shop$/
     */
    public function theProductAvailabilityIsUpdatedInTheLocalShop()
    {
        \Phake::verify(
            $this->productToShop,
            \Phake::atLeast(1)
        )->changeAvailability(\Phake::anyParameters());
    }

    /**
     * @Given /^The product was deleted in the remote shop$/
     */
    public function theProductWasDeletedInTheRemoteShop()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters())
            ->thenReturn(
                array()
            );
    }

    /**
     * @Then /^The customer is informed about the deleted product$/
     */
    public function theCustomerIsInformedAboutTheDeletedProduct()
    {
        Assertion::assertEquals(
            array(
                new Struct\Message(
                    array(
                        'message' => 'Product %product does not exist anymore.',
                        'values' => array(
                            'product' => '23-1',
                        ),
                    )
                )
            ),
            $this->checkResult->errors
        );
    }

    /**
     * @Given /^The product is deleted in the local shop$/
     */
    public function theProductIsDeletedInTheLocalShop()
    {
        \Phake::verify($this->productToShop)->delete(\Phake::anyParameters());
    }

    /**
     * @Given /^The product price has changed in the remote shop$/
     */
    public function theProductPriceHasChangedInTheRemoteShop()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters())
            ->thenReturn(
                array(
                    new Struct\Product(
                        array(
                            'shopId' => 'shop-1',
                            'sourceId' => '23-1',
                            'price' => 45.23,
                            'purchasePrice' => 23.42,
                            'purchasePriceHash' => $this->purchasePriceHash(23.42, 'apikey-shop-1'),
                            'offerValidUntil' => self::OFFER_VALID_UNTIL,
                            'fixedPrice' => $this->fixedPriceItems,
                            'currency' => 'EUR',
                            'availability' => 5,
                            'title' => 'Sindelfingen',
                            'vendor' => 'Foo',
                            'categories' => array('/others'),
                        )
                    ),
                )
            );
    }

    /**
     * @Then /^The product is reserved in the remote shop$/
     */
    public function theProductIsReservedInTheRemoteShop()
    {
        Assertion::assertTrue($this->reserveResult instanceof Struct\Reservation, "Expected a Struct\Reservation object.");
        Assertion::assertTrue($this->reserveResult->success, "Result should be success.");
        Assertion::assertEquals(0, count($this->reserveResult->messages));
        Assertion::assertEquals(1, count($this->reserveResult->orders));
    }

    /**
     * @Then /^The remote shop is asked for shipping costs$/
     */
    public function theRemoteShopIsAskedForShippingCosts()
    {
        \Phake::verify(
            $this->productFromShop,
            \Phake::atLeast(1)
        )->calculateShippingCosts(\Phake::anyParameters());
    }

    /**
     * @Then /^The shipping costs are contained in the reservation$/
     */
    public function theShippingCostsAreContainedInTheReservation()
    {
        Assertion::assertInstanceOf('Shopware\\Connect\\Struct\\Shipping', $this->reserveResult->aggregatedShippingCosts);

        foreach ($this->reserveResult->orders as $splitOrder) {
            Assertion::assertInstanceOf('Shopware\\Connect\\Struct\\Shipping', $splitOrder->shipping);
        }
    }

    /**
     * @Given /^The product changes availability between check and purchase$/
     */
    public function theProductChangesAvailabilityBetweenCheckAndPurchase()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters())
            ->thenReturn(
                array(
                    new Struct\Product(
                        array(
                            'shopId' => 'shop-1',
                            'sourceId' => '23-1',
                            'price' => 42.23,
                            'purchasePrice' => 23.42,
                            'purchasePriceHash' => $this->purchasePriceHash(23.42, 'apikey-shop-1'),
                            'offerValidUntil' => self::OFFER_VALID_UNTIL,
                            'fixedPrice' => $this->fixedPriceItems,
                            'currency' => 'EUR',
                            'availability' => 0,
                            'vendor' => 'Foo',
                            'title' => 'Sindelfingen',
                            'categories' => array('/others'),
                        )
                    ),
                )
            );
    }

    /**
     * @Given /^The buy process fails and customer is informed about this$/
     */
    public function theBuyProcessFailsAndTheCustomerIsInformedAboutThis()
    {
        Assertion::assertTrue($this->reserveResult instanceof Struct\Reservation, "Expected a Struct\Reservation object.");
        $this->dependencies->getVerificator()->verify($this->reserveResult);
        Assertion::assertFalse($this->reserveResult->success, "Result should not be success.");
        Assertion::assertNotEquals(0, count($this->reserveResult->messages));
    }

    /**
     * @Given /^The remote shop denies the buy$/
     */
    public function theRemoteShopDeniesTheBuy()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->buy(\Phake::anyParameters())
            ->thenThrow(
                new \RuntimeException("Buy denied.")
            );
    }

    /**
     * @Given /^The buy process fails$/
     */
    public function theBuyProcessFails()
    {
        if (!is_array($this->checkoutResult)) {
            throw new \RuntimeException('No checkout result available.');
        }
        foreach ($this->checkoutResult as $shopId => $value) {
            Assertion::assertFalse($value, "Buy process for $shopId did not fail.");
        }
    }

    /**
     * @Then /^The (local|remote) shop logs the transaction with Bepado$/
     */
    public function theShopLogsTheTransactionWithBepado($location)
    {
        $expectedLogMessage = $location === 'remote' ? 1 : 2;
        $logMessages = $this->logger->getLogMessages();

        Assertion::assertTrue(
            isset($logMessages[$expectedLogMessage]),
            "Expected a $location shop log message, none available."
        );
        Assertion::assertTrue(
            $logMessages[$expectedLogMessage] instanceof Struct\Order,
            "Log message should contain an Order."
        );
    }

    /**
     * @Given /^No transaction is logged$/
     */
    public function noTransactionIsLogged()
    {
        $logMessages = $this->logger->getLogMessages();

        Assertion::assertFalse(
            isset($logMessages[0]),
            "No remote shop transaction logs expected"
        );
        Assertion::assertFalse(
            isset($logMessages[1]),
            "No local shop  transaction logs expected"
        );
    }

    /**
     * @Given /^The (local|remote) shop confirms the transaction with Bepado$/
     */
    public function theShopConfirmsTheTransactionWithBepado($location)
    {
        $expectedLogMessage = $location === 'remote' ? 3 : 4;
        $logMessages = $this->logger->getLogMessages();

        Assertion::assertTrue(
            isset($logMessages[$expectedLogMessage]),
            "Expected a $location shop confirmation, none available."
        );
        Assertion::assertEquals(
            'confirm-' . ($location === 'remote' ? 1 : 2),
            $logMessages[$expectedLogMessage],
            "Log message should contain an confirmation key."
        );
    }

    /**
     * @Given /^No transactions are confirmed$/
     */
    public function noTransactionsAreConfirmed()
    {
        $logMessages = $this->logger->getLogMessages();

        Assertion::assertLessThan(
            4,
            count($logMessages),
            "No confirmation messages expected."
        );
    }

    /**
     * @Given /^The (local|remote) shop transaction logging fails$/
     */
    public function theShopTransactionLoggingFails($location)
    {
        $this->logger->breakOnLogMessage($location === 'remote' ? 1 : 2);
    }

    /**
     * @Given /^The (local|remote) shop transaction confirmation fails$/
     */
    public function theShopTransactionConfirmationFails($location)
    {
        $this->logger->breakOnLogMessage($location === 'remote' ? 3 : 4);
    }

    /**
     * @Given /^The product purchase price has changed in the remote shop$/
     */
    public function theProductPurchasePriceHasChangedInTheRemoteShop()
    {
        $methodStub = \Phake::when($this->productFromShop)
            ->getProducts(\Phake::anyParameters())
            ->thenReturn(
                array(
                    new Struct\Product(
                        array(
                            'shopId' => 'shop-1',
                            'sourceId' => '23-1',
                            'price' => 42.23,
                            'purchasePrice' => 13.37,
                            'purchasePriceHash' => $this->purchasePriceHash(13.37, 'apikey-shop-1'),
                            'offerValidUntil' => self::OFFER_VALID_UNTIL,
                            'fixedPrice' => $this->fixedPriceItems,
                            'currency' => 'EUR',
                            'availability' => 0,
                            'title' => 'Sindelfingen',
                            'vendor' => 'Foo',
                            'categories' => array('/others'),
                        )
                    ),
                )
            );
    }

    /**
     * @Given /^The product does not have a fixed price$/
     */
    public function theProductDoesNotHaveAFixedPrice()
    {
        $this->fixedPriceItems = false;
    }

    /**
     * @Then /^The customer is informed about the changed shipping costs$/
     */
    public function theCustomerIsInformedAboutTheChangedShippingCosts()
    {
        Assertion::assertTrue($this->reserveResult instanceof Struct\Reservation, "Expected a Struct\Reservation object.");
        Assertion::assertFalse($this->reserveResult->success, "Result should not be success.");
        Assertion::assertEquals(
            array(
                'shop-1' => array(
                    new Struct\Message(
                        array(
                            'message' => 'Shipping costs have changed from %oldValue to %newValue.',
                            'values' => array(
                                'oldValue' => '2.38',
                                'newValue' => '0.60',
                            ),
                        )
                    )
                )
            ),
            $this->reserveResult->messages
        );
    }

    /**
     * @Then /^The Customer is informed about not shippable order$/
     */
    public function theCustomerIsInformedAboutNotShippableOrder()
    {
        Assertion::assertTrue($this->reserveResult instanceof Struct\Reservation, "Expected a Struct\Reservation object.");
        Assertion::assertFalse($this->reserveResult->success, "Result should not be success.");
        Assertion::assertEquals(
            array(
                'shop-1' => array(new Struct\Message(
                    array( 'message' => 'Products cannot be shipped to %country.',
                        'values' => array(
                            'country' => 'DEU',
                        ),
                    )
                ))
            ),
            $this->reserveResult->messages
        );
    }

    /**
     * @Given /^The shop configured net shipping costs of "([^"]*)" and customer costs of "([^"]*)"$/
     */
    public function theShopConfiguredNetShippingCostsOfAndCustomerCostsOf($intrashopCosts, $customerCosts)
    {
        $intrashopRules = new Rules(array(
            'rules' => array(
                new Rule\FixedPrice(array(
                        'price' => $intrashopCosts,
                    )
                )
            )
        ));
        $customerRules = new Rules(array(
            'rules' => array(
                new Rule\FixedPrice(array(
                        'price' => $customerCosts,
                    )
                )
            )
        ));

        $this->gateway->storeShippingCosts('shop-1', 'shop', (string)time(), $intrashopRules, $customerRules);
        $this->remoteGateway->storeShippingCosts('shop-1', 'shop', (string)time(), $intrashopRules, $customerRules);
        // shared state madness
        $this->gateway->storeShippingCosts('shop-1', 'shop-1', (string)time(), $intrashopRules, $customerRules);
        $this->remoteGateway->storeShippingCosts('shop-1', 'shop-1', (string)time(), $intrashopRules, $customerRules);
    }

    /**
     * @Then /^The Customer is informed about net customer shipping costs "([^"]*)"$/
     */
    public function theCustomerIsInformedAboutNetCustomerShippingCosts($costs)
    {
        Assertion::assertEquals(
            $this->checkResult->aggregatedShippingCosts->shippingCosts,
            $costs,
            "Net Customer shipping cost comparison failed."
        );
    }

    /**
     * @Given /^The intrashop shipping costs are "([^"]*)" for shop "([^"]*)"$/
     */
    public function theIntrashopShippingCostsAre($costs, $shop)
    {
        // @TODO: Must be clarified on how to handle this
        return;
        Assertion::assertEquals(
            $this->checkResult->orders['shop-' . $shop]->shippingCosts,
            $costs,
            "Net Intrashop shipping cost comparison failed."
        );
    }

    /**
     * @Given /^The remote shop recieves an anonymized email$/
     */
    public function theRemoteShopRecievesAnAnonymizedEmail()
    {
        $remoteOrder = $this->remoteGateway->getOrder($this->reservationId);

        Assertion::assertEquals(
            'marketplace-shop-3a10b4a1798feed276bf434f6d49a2d4@mail.bepado.com',
            $remoteOrder->deliveryAddress->email
        );
    }
}
