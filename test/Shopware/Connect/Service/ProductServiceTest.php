<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\Gateway;
use Shopware\Connect\ProductFromShop;
use Shopware\Connect\ProductToShop;
use Shopware\Connect\Struct;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    public function testReplicateAvailabilityToShopChange()
    {
        $gateway = $this->createMock(Gateway::class);
        $service = new ProductService(
            $gateway, $gateway, $gateway,
            $toShop = $this->createMock(ProductToShop::class),
            $fromShop = $this->createMock(ProductFromShop::class),
            $export = $this->createMock(Export::class)
        );

        $toShop->expects($this->atLeastOnce())->method('changeAvailability')->with(10, 'foo123', 100);

        $service->replicate(array(
            new Struct\Change\ToShop\Availability(array(
                'shopId' => 10,
                'sourceId' => 'foo123',
                'availability' => 100,
            ))
        ));
    }

    public function testRepliacteUpdateToShopChange()
    {
        $gateway = $this->createMock(Gateway::class);
        $service = new ProductService(
            $gateway, $gateway, $gateway,
            $toShop = $this->createMock(ProductToShop::class),
            $fromShop = $this->createMock(ProductFromShop::class),
            $export = $this->createMock(Export::class)
        );
        $product = new Struct\ProductUpdate();

        $toShop->expects($this->atLeastOnce())->method('update')->with(10, 'foo123', $product);

        $service->replicate(array(
            new Struct\Change\ToShop\Update(array(
                'shopId' => 10,
                'sourceId' => 'foo123',
                'product' => $product,
            ))
        ));
    }

    public function testRepliacteUpdateOrderStatusToShopChange()
    {
        $gateway = $this->createMock(Gateway::class);
        $service = new ProductService(
            $gateway, $gateway, $gateway,
            $toShop = $this->createMock(ProductToShop::class),
            $fromShop = $this->createMock(ProductFromShop::class),
            $export = $this->createMock(Export::class)
        );
        $toShop->expects($this->atLeastOnce())->method('updateOrderStatus')->with(10, 'completed', 'foo123');

        $service->replicate(array(
            new Struct\Change\ToShop\UpdateOrderStatus(array(
                'localOrderId' => 10,
                'trackingNumber' => 'foo123',
                'orderStatus' => 'completed',
            ))
        ));
    }
}
