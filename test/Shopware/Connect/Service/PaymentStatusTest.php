<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\Gateway;
use Shopware\Connect\ProductFromShop;
use Shopware\Connect\Struct;
use Shopware\Connect\Struct\Change\FromShop\UpdatePaymentStatus;

class PaymentStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Connect\Service\PaymentStatus
     */
    private $service;

    /**
     * @var \Shopware\Connect\Gateway
     */
    private $gateway;

    /**
     * @var \Shopware\Connect\ProductFromShop
     */
    private $fromShop;

    /**
     * @var \Shopware\Connect\Gateway\ShopConfiguration
     */
    private $shopConfiguration;

    public function setUp()
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->fromShop = $this->createMock(ProductFromShop::class);
        $this->shopConfiguration = $this->createMock(Gateway\ShopConfiguration::class);

        $this->service = new PaymentStatus(
            $this->gateway,
            $this->fromShop,
            $this->shopConfiguration
        );
    }

    public function testUpdatePaymentStatus()
    {
        $status = new Struct\PaymentStatus([
            'revision' => '1234'
        ]);
        $this->fromShop->expects($this->atLeastOnce())->method('updatePaymentStatus')->with($status);
        $this->shopConfiguration->expects($this->atLeastOnce())->method('setConfig')->with(PaymentStatus::PAYMENT_REVISION, '1234');

        $this->service->replicate([
            $status
        ]);
    }

    public function testGetChanges()
    {
        $since = 3;
        $limit = 5;

        $this->gateway->method('getNextPaymentStatusChanges')->with($since, $limit)->willReturn([
            new UpdatePaymentStatus(['paymentStatus' => 'received'])
        ]);

        $this->gateway->expects($this->atLeastOnce())->method('getNextPaymentStatusChanges')->with($since, $limit);
        $this->gateway->expects($this->atLeastOnce())->method('cleanChangesUntil')->with($since);

        $changes = $this->service->getChanges($since, $limit);

        self::assertCount(1, $changes);
        $change = reset($changes);
        self::assertInstanceOf('Shopware\Connect\Struct\Change\FromShop\UpdatePaymentStatus', $change);
        self::assertEquals('received', $change->paymentStatus);
    }
}
