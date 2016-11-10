<?php
return new \Shopware\Connect\Struct\RpcCall(
    array(
        "service" => "ProductService",
        "command" => "testOrder",
        "arguments" => array(
            new \Shopware\Connect\Struct\Order(array(
                'shipping' => new \Shopware\Connect\Struct\Shipping(array(
                    'shippingCosts' => 5.,
                    'grossShippingCosts' => 6.,
                )),
            ))
        )
    )
);
