<?php

namespace Shopware\Connect\Service;

class ShoppingTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyArrayMessageResponseDuringReserve()
    {
        $shopping = new Shopping(
            $factory = \Phake::mock('Shopware\Connect\ShopFactory'),
            \Phake::mock('Shopware\Connect\ChangeVisitor'),
            \Phake::mock('Shopware\Connect\ProductToShop'),
            \Phake::mock('Shopware\Connect\Logger'),
            \Phake::mock('Shopware\Connect\ErrorHandler'),
            \Phake::mock('Shopware\Connect\Gateway\ShopConfiguration')
        );

        $gateway = \Phake::mock('Shopware\Connect\ShopGateway');
        \Phake::when($factory)->getShopGateway(1)->thenReturn($gateway);
        \Phake::when($factory)->getShopGateway(2)->thenReturn($gateway);
        \Phake::when($gateway)->reserveProducts(\Phake::anyParameters())->thenReturn(
            new \Shopware\Connect\Struct\CheckResult()
        );

        $shippingCosts = new \Shopware\Connect\Struct\Shipping(array('shippingCosts' => 1, 'grossShippingCosts' => 2));
        \Phake::when($gateway)->checkProducts(\Phake::anyParameters())
            ->thenReturn(new \Shopware\Connect\Struct\CheckResult(
                array('shippingCosts' => array($shippingCosts), 'aggregatedShippingCosts' => $shippingCosts)
            ))
        ;

        $return = $shopping->reserveProducts($this->createOrder());

        $this->assertFalse($return->success);
        $this->assertEquals(2, count($return->messages));
        $this->assertEquals('An error occured on the remote shop during reservation, order is cancelled.', $return->messages[1][0]->message);
        $this->assertEquals('An error occured on the remote shop during reservation, order is cancelled.', $return->messages[2][0]->message);
    }

    public function testReserveProducts()
    {
        $shopping = new Shopping(
            $factory = \Phake::mock('Shopware\Connect\ShopFactory'),
            \Phake::mock('Shopware\Connect\ChangeVisitor'),
            \Phake::mock('Shopware\Connect\ProductToShop'),
            \Phake::mock('Shopware\Connect\Logger'),
            \Phake::mock('Shopware\Connect\ErrorHandler'),
            \Phake::mock('Shopware\Connect\Gateway\ShopConfiguration')
        );

        $reservationId = uniqid();
        $gateway = \Phake::mock('Shopware\Connect\ShopGateway');
        \Phake::when($factory)->getShopGateway(1)->thenReturn($gateway);
        \Phake::when($factory)->getShopGateway(2)->thenReturn($gateway);
        \Phake::when($gateway)->reserveProducts(\Phake::anyParameters())->thenReturn($reservationId);


        \Phake::when($gateway)->checkProducts(\Phake::anyParameters())
            ->thenReturn(new \Shopware\Connect\Struct\CheckResult(array(
                    'shippingCosts' => array(
                        new \Shopware\Connect\Struct\Shipping(array('shopId' => 1, 'shippingCosts' => 3, 'grossShippingCosts' => 5)),
                        new \Shopware\Connect\Struct\Shipping(array('shopId' => 2, 'shippingCosts' => 4, 'grossShippingCosts' => 8))
                    ),
                    'aggregatedShippingCosts' => new \Shopware\Connect\Struct\Shipping(array('shippingCosts' => 7, 'grossShippingCosts' => 13)),
                )
            ));

        $return = $shopping->reserveProducts($this->createOrder());

        $this->assertTrue($return->success);
        $this->assertEquals(0, count($return->messages));
        $this->assertEquals(2, count($return->orders));

        $this->assertEquals(1, $return->orders[1]->shipping->shopId);
        $this->assertEquals($reservationId, $return->orders[1]->reservationId);
        $this->assertEquals(3, $return->orders[1]->shipping->shippingCosts);
        $this->assertEquals(5, $return->orders[1]->shipping->grossShippingCosts);

        $this->assertEquals(2, $return->orders[2]->shipping->shopId);
        $this->assertEquals($reservationId, $return->orders[2]->reservationId);
        $this->assertEquals(4, $return->orders[2]->shipping->shippingCosts);
        $this->assertEquals(8, $return->orders[2]->shipping->grossShippingCosts);
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
