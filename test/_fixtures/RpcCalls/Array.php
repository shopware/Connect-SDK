<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'ArrayService',
        'command' => 'testArray',
        'arguments' => [
            [
                'foo',
                'bar',
                23,
                42.3,
                true,
                false,
                null,
                []
            ]
        ]
    ]
);
