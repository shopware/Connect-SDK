<?php

namespace Shopware\Connect\Struct;

class OrderStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testValidOrderStatus()
    {
        $status = new OrderStatus([
            'id' => '1234',
            'status' => OrderStatus::STATE_OPEN,
            'tracking' => new Tracking([
                'id' => '1234',
                'url' => 'http://foo',
                'vendor' => Tracking::VENDOR_FEDEX,
            ])
        ]);

        $this->verificator->verify($status);
    }

    public function testInvalidOrderStatus()
    {
        $status = new OrderStatus([
            'id' => '1234',
            'status' => 'foo',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid order state given: foo. Expected one of: open, in_process, delivered, canceled, error');

        $this->verificator->verify($status);
    }

    public function setUp()
    {
        $this->verificator = new VerificatorDispatcher(
            [
                'Shopware\\Connect\\Struct\\Tracking' => new Verificator\Tracking(),
                'Shopware\\Connect\\Struct\\OrderStatus' => new Verificator\OrderStatus(),
            ]
        );
    }
}
