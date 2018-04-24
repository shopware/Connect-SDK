<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\ChangeVisitor;

use Shopware\Connect\ChangeVisitor;
use Shopware\Connect\Struct;

/**
 * Visits intershop changes ito messages
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Message extends ChangeVisitor
{
    /**
     * Verificator
     *
     * @var VerificatorDispatcher
     */
    protected $verificator;

    public function __construct(Struct\VerificatorDispatcher $verificator)
    {
        $this->verificator = $verificator;
    }

    /**
     * Visit changes
     *
     * @param array $changes
     * @return Struct\Message[]
     */
    public function visit(array $changes)
    {
        $messages = [];
        foreach ($changes as $shop => $change) {
            $this->verificator->verify($change);

            switch (true) {
                case ($change instanceof Struct\Change\InterShop\Update):
                    /**
                     * The else block can be removed when all shops use version greater than 2.0.3
                     */
                    if ($change->oldProduct !== null) {
                        $messages[] = new Struct\Message([
                            'message' => 'The price of product %product has changed.',
                            'values' => [
                                'product' => $change->oldProduct->title
                            ]
                        ]);
                    } else {
                        $messages[] = new Struct\Message([
                            'message' => 'The price of product %product has changed.',
                            'values' => [
                                'product' => $change->sourceId
                            ]
                        ]);
                    }
                    break;

                case ($change instanceof Struct\Change\InterShop\Unavailable):
                    $messages[] = new Struct\Message([
                        'message' => 'Availability of product %product changed to %availability.',
                        'values' => [
                            'shopId' => $change->shopId,
                            'product' => $change->sourceId,
                            'availability' => $change->availability,
                        ]
                    ]);
                    break;

                case ($change instanceof Struct\Change\InterShop\Delete):
                    $messages[] = new Struct\Message([
                        'message' => 'Product %product does not exist anymore.',
                        'values' => [
                            'product' => $change->sourceId
                        ]
                    ]);
                    break;

                default:
                    throw new \RuntimeException(
                        'No visitor found for ' . get_class($change)
                    );
            }
        }

        return $messages;
    }
}
