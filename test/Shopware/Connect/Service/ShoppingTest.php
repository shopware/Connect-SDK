<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\ChangeVisitor;
use Shopware\Connect\ErrorHandler;
use Shopware\Connect\Gateway\ShopConfiguration;
use Shopware\Connect\Logger;
use Shopware\Connect\ProductToShop;
use Shopware\Connect\ShopFactory;
use Shopware\Connect\ShopGateway;

class ShoppingTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyArrayMessageResponseDuringReserve()
    {
        $shopping = new Shopping(
            $factory = $this->createMock(ShopFactory::class),
            $this->createMock(ChangeVisitor::class),
            $this->createMock(ProductToShop::class),
            $this->createMock(Logger::class),
            $this->createMock(ErrorHandler::class),
            $this->createMock(ShopConfiguration::class)
        );

        $gateway = $this->createMock(ShopGateway::class);
        $factory->method('getShopGateway')
            ->withConsecutive([1],[2])
            ->willReturn($gateway);
        $gateway->method('reserveProducts')->with($this->anything())->willReturn(
            new \Shopware\Connect\Struct\CheckResult()
        );

        $shippingCosts = new \Shopware\Connect\Struct\Shipping(array('shippingCosts' => 1, 'grossShippingCosts' => 2));
        $gateway->method('checkProducts')->with($this->anything())
            ->willReturn(new \Shopware\Connect\Struct\CheckResult(
                array('shippingCosts' => array($shippingCosts), 'aggregatedShippingCosts' => $shippingCosts)
            ))
        ;

        $return = $shopping->reserveProducts($this->createOrder());

        self::assertFalse($return->success);
        self::assertEquals(2, count($return->messages));
        self::assertEquals('An error occured on the remote shop during reservation, order is cancelled.', $return->messages[1][0]->message);
        self::assertEquals('An error occured on the remote shop during reservation, order is cancelled.', $return->messages[2][0]->message);
    }

    public function testReserveProducts()
    {
        $shopping = new Shopping(
            $factory = $this->createMock(ShopFactory::class),
            $this->createMock(ChangeVisitor::class),
            $this->createMock(ProductToShop::class),
            $this->createMock(Logger::class),
            $this->createMock(ErrorHandler::class),
            $this->createMock(ShopConfiguration::class)
        );

        $reservationId = uniqid();
        $gateway = $this->createMock(ShopGateway::class);
        $factory->method('getShopGateway')->withConsecutive([1],[2])->willReturn($gateway);
        $gateway->method('reserveProducts')->with($this->anything())->willReturn($reservationId);


        $gateway->method('checkProducts')->with($this->anything())
            ->willReturn(new \Shopware\Connect\Struct\CheckResult(array(
                    'shippingCosts' => array(
                        new \Shopware\Connect\Struct\Shipping(array('shopId' => 1, 'shippingCosts' => 3, 'grossShippingCosts' => 5)),
                        new \Shopware\Connect\Struct\Shipping(array('shopId' => 2, 'shippingCosts' => 4, 'grossShippingCosts' => 8))
                    ),
                    'aggregatedShippingCosts' => new \Shopware\Connect\Struct\Shipping(array('shippingCosts' => 7, 'grossShippingCosts' => 13)),
                )
            ));

        $return = $shopping->reserveProducts($this->createOrder());

        self::assertTrue($return->success);
        self::assertEquals(0, count($return->messages));
        self::assertEquals(2, count($return->orders));

        self::assertEquals(1, $return->orders[1]->shipping->shopId);
        self::assertEquals($reservationId, $return->orders[1]->reservationId);
        self::assertEquals(3, $return->orders[1]->shipping->shippingCosts);
        self::assertEquals(5, $return->orders[1]->shipping->grossShippingCosts);

        self::assertEquals(2, $return->orders[2]->shipping->shopId);
        self::assertEquals($reservationId, $return->orders[2]->reservationId);
        self::assertEquals(4, $return->orders[2]->shipping->shippingCosts);
        self::assertEquals(8, $return->orders[2]->shipping->grossShippingCosts);
    }

    private function createOrder()
    {
        return new \Shopware\Connect\Struct\Order(
            array(
                'deliveryAddress' => new \Shopware\Connect\Struct\Address(),
                'products' => array(
                    new \Shopware\Connect\Struct\OrderItem(
                        array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(
                                array(
                                    'shopId' => 1,
                                    'freeDelivery' => false,
                                    'vat' => 0.07,
                                )
                            ),
                        )
                    ),
                    new \Shopware\Connect\Struct\OrderItem(
                        array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(
                                array(
                                    'shopId' => 2,
                                    'freeDelivery' => false,
                                    'vat' => 0.19,
                                )
                            ),
                        )
                    ),
                ),
            )
        );
    }
}
