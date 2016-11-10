<?php
return new \Shopware\Connect\Struct\RpcCall(
    array(
        "service" => "ProductService",
        "command" => "testProduct",
        "arguments" => array(
            new \Shopware\Connect\Rpc\ShopProduct(
                array(
                    'availability' => '0',
                    'sku' => 'SKU00001',
                    'categories' => array(),
                    'currency' => 'EUR',
                    'images' => unserialize('a:1:{i:0;s:4496:"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";}'),
                    'longDescription' => 'Original SecretActive Ingredients: Aluminum Zirconium Trichlorohydrex Gly (19%) (Anhydrous). Inactive Ingredients: Cyclopentasiloxane, Stearyl Alcohol, C12-15 Alkyl Benzoate, PPG-14 Butyl Ether, Hydrogenated Castor Oil, Petrolatum, Phenyl Trimethicone, Talc, Cyclodextrin, Fragrance, Mineral Oil, Behenyl Alcohol.',
                    'price' => '4.99',
                    'revisionId' => '20120905000000000002',
                    'shopId' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                    'shortDescription' => 'invisible solid Duft: POWDER FRESH Lang anhaltender Schutz',
                    'sourceId' => 'B00005308K',
                    'title' => 'Secret pH Balanced INVISIBLE Solid powder fresh',
                    'url' => 'http://www.amazon.de/Secret-Balanced-INVISIBLE-Solid-powder/dp/B00005308K%3FSubscriptionId%3DAKIAILJJASZAP6AQ5GNQ%26tag%3Dws%26linkCode%3Dsp1%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB00005308K',
                    'vendor' => 'Secret',
                    'language' => 'de',
                )
            )
        )
    )
);

