<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\Struct;
use Shopware\Connect\Struct\Change\FromShop\UpdatePaymentStatus;

class PaymentStatusTest extends \PHPUnit_Framework_TestCase
{
    private $service;
    private $gateway;
    private $fromShop;
    private $shopConfiguration;

    public function setUp()
    {
        $this->gateway = \Phake::mock('Shopware\Connect\Gateway');
        $this->fromShop = \Phake::mock('Shopware\Connect\ProductFromShop');
        $this->shopConfiguration = \Phake::mock('Shopware\Connect\Gateway\ShopConfiguration');

        $this->service = new PaymentStatus(
            $this->gateway,
            $this->fromShop,
            $this->shopConfiguration
        );
    }

    public function testUpdatePaymentStatus()
    {
        $this->service->replicate(array(
            $status = new Struct\PaymentStatus(array(
                'revision' => '1234'
            ))
        ));

        \Phake::verify($this->fromShop)->updatePaymentStatus($status);
        \Phake::verify($this->shopConfiguration)->setConfig(PaymentStatus::PAYMENT_REVISION, '1234');
    }

    public function testGetChanges()
    {
        $since = 3;
        $limit = 5;

        \Phake::when($this->gateway)->getNextPaymentStatusChanges($since, $limit)->thenReturn(array(
            new UpdatePaymentStatus(array('paymentStatus' => 'received'))
        ));

        $changes = $this->service->getChanges($since, $limit);

        $this->assertCount(1, $changes);
        $change = reset($changes);
        $this->assertInstanceOf('Shopware\Connect\Struct\Change\FromShop\UpdatePaymentStatus', $change);
        $this->assertEquals('received', $change->paymentStatus);

        \Phake::verify($this->gateway)->getNextPaymentStatusChanges($since, $limit);
        \Phake::verify($this->gateway)->cleanChangesUntil($since);
    }
}
