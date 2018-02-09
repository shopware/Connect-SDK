<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'ProductService',
        'command' => 'testProduct',
        'arguments' => [
            new \Exception('Exception message', 23)
        ]
    ]
);
