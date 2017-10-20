<?php

namespace Shopware\Connect\ChangeVisitor;

use Shopware\Connect\Struct;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testPriceDifferencesProducesMessage()
    {
        $dispatcher = $this->createMock(Struct\VerificatorDispatcher::class);
        $visitor = new Message($dispatcher);
        $messages = $visitor->visit(array(
            new Struct\Change\InterShop\Update(array(
                'oldProduct' => new Struct\Product(array(
                    'availability' => 0,
                    'title' => 'oldTitle'
                )),
                'product' => new Struct\Product(array(
                    'availability' => 0,
                    'title' => 'newTitle'
                )),
                'sourceId' => 'FOO1234',
            ))
        ));

        $expectedMessages = array(new Struct\Message(array(
            'message' => 'The price of product %product has changed.',
            'values' => array('product' => 'oldTitle'),
        )));

        self::assertEquals($expectedMessages, $messages);
    }

    public function testAvailabilityZeroProducesMessage() {
        $dispatcher = $this->createMock(Struct\VerificatorDispatcher::class);
        $visitor = new Message($dispatcher);
        $messages = $visitor->visit(array(
            new Struct\Change\InterShop\Unavailable(array(
                'availability' => 0,
                'sourceId' => 'FOO1234',
            ))
        ));

        $expectedMessages = array(new Struct\Message(array(
            'message' => 'Availability of product %product changed to %availability.',
            'values' => array('product' => 'FOO1234', 'availability' => 0),
        )));

        self::assertEquals($expectedMessages, $messages);
    }
}
