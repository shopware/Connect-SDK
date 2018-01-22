<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'BooleanService',
        'command' => 'testBoolean',
        'arguments' => [
            true
        ]
    ]
);
