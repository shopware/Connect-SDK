<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'IntegerService',
        'command' => 'testInteger',
        'arguments' => [
            42
        ]
    ]
);
