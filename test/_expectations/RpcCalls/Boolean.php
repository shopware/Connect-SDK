<?php
return new \Shopware\Connect\Struct\RpcCall(
    array(
        "service" => "BooleanService",
        "command" => "testBoolean",
        "arguments" => array(
            true
        )
    )
);
