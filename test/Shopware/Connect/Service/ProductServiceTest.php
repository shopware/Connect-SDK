<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\Struct;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    public function testReplicateAvailabilityToShopChange()
    {
        $gateway = \Phake::mock('Shopware\Connect\Gateway');
        $service = new ProductService(
            $gateway, $gateway, $gateway,
            $toShop = \Phake::mock('Shopware\Connect\ProductToShop'),
            $fromShop = \Phake::mock('Shopware\Connect\ProductFromShop'),
            $export = \Phake::mock('Shopware\Connect\Service\Export')
        );

        $service->replicate(array(
            new Struct\Change\ToShop\Availability(array(
                'shopId' => 10,
                'sourceId' => 'foo123',
                'availability' => 100,
            ))
        ));

        \Phake::verify($toShop)->changeAvailability(10, 'foo123', 100);
    }

    public function testRepliacteUpdateToShopChange()
    {
        $gateway = \Phake::mock('Shopware\Connect\Gateway');
        $service = new ProductService(
            $gateway, $gateway, $gateway,
            $toShop = \Phake::mock('Shopware\Connect\ProductToShop'),
            $fromShop = \Phake::mock('Shopware\Connect\ProductFromShop'),
            $export = \Phake::mock('Shopware\Connect\Service\Export')
        );

        $service->replicate(array(
            new Struct\Change\ToShop\Update(array(
                'shopId' => 10,
                'sourceId' => 'foo123',
                'product' => $product = new Struct\ProductUpdate(),
            ))
        ));

        \Phake::verify($toShop)->update(10, 'foo123', $product);
    }

    public function testRepliacteUpdateOrderStatusToShopChange()
    {
        $gateway = \Phake::mock('Shopware\Connect\Gateway');
        $service = new ProductService(
            $gateway, $gateway, $gateway,
            $toShop = \Phake::mock('Shopware\Connect\ProductToShop'),
            $fromShop = \Phake::mock('Shopware\Connect\ProductFromShop'),
            $export = \Phake::mock('Shopware\Connect\Service\Export')
        );

        $service->replicate(array(
            new Struct\Change\ToShop\UpdateOrderStatus(array(
                'localOrderId' => 10,
                'trackingNumber' => 'foo123',
                'orderStatus' => 'completed',
            ))
        ));

        \Phake::verify($toShop)->updateOrderStatus(10, 'foo123', 'completed');
    }
}
