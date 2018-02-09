<?php

return new \Shopware\Connect\Struct\RpcCall(
    [
        'service' => 'NullService',
        'command' => 'testNull',
        'arguments' => [
            null
        ]
    ]
);
