<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'ProductService',
        'command' => 'testOrder',
        'arguments' => [
            new \Shopware\Connect\Struct\Order([
                'shipping' => new \Shopware\Connect\Struct\Shipping([
                    'shippingCosts' => 5.,
                    'grossShippingCosts' => 6.,
                ]),
            ])
        ]
    ]
);
