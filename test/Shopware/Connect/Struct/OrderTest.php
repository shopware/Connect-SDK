<?php

namespace Shopware\Connect\Struct;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    public function testProductsOrderItemsBackwardsCompatbility1()
    {
        $orderItems = array(new OrderItem());

        $order = new Order(array(
            'products' => $orderItems,
        ));

        $this->assertSame($orderItems, $order->orderItems);
    }

    public function testProductsOrderItemsBackwardsCompatbility2()
    {
        $orderItems = array(new OrderItem());

        $order = new Order(array(
            'orderItems' => $orderItems,
        ));

        $this->assertSame($orderItems, $order->products);
    }

    public function testProductsGetSetMagic()
    {
        $orderItems = array(new OrderItem());

        $order = new Order();
        $order->products = $orderItems;

        $this->assertSame($orderItems, $order->products);
    }

    public function testAppendProducts()
    {
        $order = new Order();
        $order->products[] = $item = new OrderItem;

        $this->assertSame($item, $order->products[0]);
    }
}
