<?php

namespace Shopware\Connect\ChangeVisitor;

use Shopware\Connect\Struct;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testPriceDifferencesProducesMessage()
    {
        $dispatcher = $this->createMock(Struct\VerificatorDispatcher::class);
        $visitor = new Message($dispatcher);
        $messages = $visitor->visit([
            new Struct\Change\InterShop\Update([
                'oldProduct' => new Struct\Product([
                    'availability' => 0,
                    'title' => 'oldTitle'
                ]),
                'product' => new Struct\Product([
                    'availability' => 0,
                    'title' => 'newTitle'
                ]),
                'sourceId' => 'FOO1234',
            ])
        ]);

        $expectedMessages = [new Struct\Message([
            'message' => 'The price of product %product has changed.',
            'values' => ['product' => 'oldTitle'],
        ])];

        self::assertEquals($expectedMessages, $messages);
    }

    public function testAvailabilityZeroProducesMessage()
    {
        $dispatcher = $this->createMock(Struct\VerificatorDispatcher::class);
        $visitor = new Message($dispatcher);
        $messages = $visitor->visit([
            new Struct\Change\InterShop\Unavailable([
                'availability' => 0,
                'sourceId' => 'FOO1234',
            ])
        ]);

        $expectedMessages = [new Struct\Message([
            'message' => 'Availability of product %product changed to %availability.',
            'values' => ['product' => 'FOO1234', 'availability' => 0],
        ])];

        self::assertEquals($expectedMessages, $messages);
    }
}
