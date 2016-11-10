<?php
return new \Shopware\Connect\Struct\RpcCall(
    array(
        "service" => "IntegerService",
        "command" => "testInteger",
        "arguments" => array(
            42
        )
    )
);
