<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\Struct;

class PaymentStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdatePaymentStatus()
    {
        $service = new PaymentStatus(
            $gateway = \Phake::mock('Shopware\Connect\Gateway'),
            $fromShop = \Phake::mock('Shopware\Connect\ProductFromShop'),
            $shopConfiguration = \Phake::mock('Shopware\Connect\Gateway\ShopConfiguration')
        );

        $service->replicate(array(
            $status = new Struct\PaymentStatus(array(
                'revision' => '1234'
            ))
        ));

        \Phake::verify($fromShop)->updatePaymentStatus($status);
        \Phake::verify($shopConfiguration)->setConfig(PaymentStatus::PAYMENT_REVISION, '1234');
    }
}
