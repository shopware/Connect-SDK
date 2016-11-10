<?php
return new \Shopware\Connect\Struct\RpcCall(
    array(
        "service" => "FloatService",
        "command" => "testFloat",
        "arguments" => array(
            42.3
        )
    )
);
