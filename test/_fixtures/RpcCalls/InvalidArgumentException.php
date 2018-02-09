<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'ProductService',
        'command' => 'testProduct',
        'arguments' => [
            new \InvalidArgumentException('Exception message', 23)
        ]
    ]
);
