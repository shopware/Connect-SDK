<?php

return new \Shopware\Connect\Struct\RpcCall(
    array(
        "service" => "ProductService",
        "command" => "testProduct",
        "arguments" => array(
            new \InvalidArgumentException("Exception message", 23)
        )
    )
);
