<?php

namespace Shopware\Connect\ChangeVisitor;

use Shopware\Connect\Struct;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testAvailabilityZeroProducesMessage()
    {
        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');
        $visitor = new Message($dispatcher);
        $messages = $visitor->visit(array(
            new Struct\Change\InterShop\Update(array(
                'oldProduct' => new Struct\Product(array(
                    'availability' => 0,
                )),
                'product' => new Struct\Product(array(
                    'availability' => 0,
                )),
                'sourceId' => 'FOO1234',
            ))
        ));

        $expectedMessages = array(new Struct\Message(array(
            'message' => 'Availability of product %product changed to %availability.',
            'values' => array('product' => 'FOO1234', 'availability' => 0),
        )));

        $this->assertEquals($expectedMessages, $messages);
    }
}
