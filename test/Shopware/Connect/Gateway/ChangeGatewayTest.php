<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Gateway;

use Shopware\Connect\Struct\Change\FromShop\Availability;
use Shopware\Connect\Struct\Change\FromShop\Delete;
use Shopware\Connect\Struct\Change\FromShop\Insert;
use Shopware\Connect\Struct\Change\FromShop\StreamDelete;
use Shopware\Connect\Struct\Change\FromShop\UpdatePaymentStatus;
use Shopware\Connect\Struct\Change\FromShop\StreamAssignment;
use Shopware\Connect\Struct\Change\FromShop\MakeMainVariant;
use Shopware\Connect\Struct\PaymentStatus as PaymentStatusStruct;
use Shopware\Connect\Struct\Product;

/**
 * Common tests for the change gateway implementations.
 */
abstract class ChangeGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testGetNextChangesReturnsExpectedNextResult()
    {
        $gateway = $this->createChangeGateway();
        $gateway->recordInsert(
            'avocado-10906',
            md5('avocado-10906'),
            '1358342508.3692500266',
            new Product()
        );
        $gateway->recordDelete(
            'avocado-10906',
            '1358342508.7466800423'
        );

        $this->assertEquals(
            array(
                new Delete(
                    array(
                        'sourceId' => 'avocado-10906',
                        'revision' => '1358342508.7466800423'
                    )
                )
            ),
            $gateway->getNextChanges('1358342508.7466800422', 10)
        );
    }

    /**
     * @return void
     */
    public function testGetNextChangesReturnsNotResultForEqualRevision()
    {
        $gateway = $this->createChangeGateway();
        $gateway->recordInsert(
            'avocado-10906',
            md5('avocado-10906'),
            '1358342508.3692500266',
            new Product()
        );
        $gateway->recordDelete(
            'avocado-10906',
            '1358342508.7466800423'
        );

        $this->assertSame(
            array(),
            $gateway->getNextChanges('1358342508.7466800423', 10)
        );
    }

    public function testGetNextAvailabilityChanges()
    {
        $gateway = $this->createChangeGateway();
        $gateway->recordAvailabilityUpdate(
            'avocado-10906',
            md5('avocado-10906'),
            '1358342508.3692500266',
            new Product(array('availability' => 10))
        );

        $this->assertEquals(
            array(
                new Availability(
                    array(
                        'sourceId' => 'avocado-10906',
                        'revision' => '1358342508.3692500266',
                        'availability' => 10
                    )
                )
            ),
            $gateway->getNextChanges('1358342508.3692500265', 10)
        );
    }

    public function testGetNextStreamAssignmentChanges()
    {
        $gateway = $this->createChangeGateway();
        $gateway->recordStreamAssignment(
            'avocado-10906',
            '1358342509.1492500266',
            array(
                'cosmetics' => 'Cosmetics',
                'body_care' => 'Body care',
            ),
            'avocado-10906-group'
        );

        $gateway->recordStreamAssignment(
            'avocado-10907',
            '1358342509.1492500267',
            array(
                'cosmetics' => 'Cosmetics',
                'body_care' => 'Body care',
            )
        );

        $this->assertEquals(
            array(
                new StreamAssignment(
                    array(
                        'sourceId' => 'avocado-10906',
                        'revision' => '1358342509.1492500266',
                        'supplierStreams' => array(
                            'cosmetics' => 'Cosmetics',
                            'body_care' => 'Body care',
                        ),
                        'groupId' => 'avocado-10906-group',
                    )
                ),
                new StreamAssignment(
                    array(
                        'sourceId' => 'avocado-10907',
                        'revision' => '1358342509.1492500267',
                        'supplierStreams' => array(
                            'cosmetics' => 'Cosmetics',
                            'body_care' => 'Body care',
                        ),
                    )
                )
            ),
            $gateway->getNextChanges('1358342509.1492500265', 10)
        );
    }

    public function testGetNextStreamDeleteChanges()
    {
        $gateway = $this->createChangeGateway();
        $gateway->recordStreamDelete(
            'cosmetics',
            '1358342508.7466800423'
        );

        $this->assertEquals(
            array(
                new StreamDelete(
                    array(
                        'sourceId' => 'cosmetics',
                        'revision' => '1358342508.7466800423',
                    )
                )
            ),
            $gateway->getNextChanges('1358342508.7466800422', 10)
        );
    }

    public function testGetNextMainVariantChanges()
    {
        $gateway = $this->createChangeGateway();
        $gateway->makeMainVariant(
            'avocado-10906-2',
            '1358342509.1492500266',
            'avocado-10906'
        );

        $this->assertEquals(
            array(
                new MakeMainVariant(
                    array(
                        'sourceId' => 'avocado-10906-2',
                        'revision' => '1358342509.1492500266',
                        'groupId' => 'avocado-10906'
                    )
                )
            ),
            $gateway->getNextChanges('1358342509.1492500265', 10)
        );
    }

    public function testGetNextPaymentUpdateChanges()
    {
        $gateway = $this->createChangeGateway();

        $paymentStatus = new PaymentStatusStruct(array(
            'localOrderId' => 'order-10906',
            'paymentStatus' => PaymentStatusStruct::PAYMENT_OPEN,
        ));

        $gateway->updatePaymentStatus(
            '1358342509.1492500266',
            $paymentStatus
        );

        $this->assertEquals(
            array(
                new UpdatePaymentStatus(array(
                    'sourceId' => 'order-10906',
                    'paymentStatus' => $paymentStatus,
                    'revision' => '1358342509.1492500266',
                ))
            ),
            $gateway->getNextPaymentStatusChanges('1358342509.1492500265', 10)
        );
    }

    /**
     * Factory method which creates our concrete SUT instance.
     *
     * @return \Shopware\Connect\Gateway\ChangeGateway
     */
    abstract protected function createChangeGateway();
}
