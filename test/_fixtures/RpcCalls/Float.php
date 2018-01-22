<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'FloatService',
        'command' => 'testFloat',
        'arguments' => [
            42.3
        ]
    ]
);
