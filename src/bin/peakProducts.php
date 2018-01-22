<?php

namespace Bepado\DevTools;

use Bepado\SDK\Struct;
use Bepado\SDK\Rpc;

function peakProducts($shopUrl, $apiKey, array $ids)
{
    return call($shopUrl, $apiKey, 'products', 'peakProducts', [$ids]);
}

function call($shopUrl, $apiKey, $service, $method, $args)
{
    $marshaller = new Rpc\Marshaller\CallMarshaller\XmlCallMarshaller(
        new \Bepado\SDK\XmlHelper(),
        new Rpc\Marshaller\Converter\ExceptionToErrorConverter()
    );
    $xml = $marshaller->marshal(
        new Struct\RpcCall(
            [
                'service' => $service,
                'command' => $method,
                'arguments' => $args,
            ]
        )
    );
    request($shopUrl, $xml, $apiKey);
}

function request($shopUrl, $xml, $apiKey)
{
    $requestDate     = gmdate('D, d M Y H:i:s', time()) . ' GMT';
    $nonce = generateNonce($requestDate, $xml, $apiKey);

    $httpClient = new \Bepado\SDK\HttpClient\Stream($shopUrl);

    $authHeaderContent = 'SharedKey party="connect",nonce="' . $nonce . '"';

    $headers = [
        'Authentication: ' . $authHeaderContent,
        'X-Shopware-Connect-Authorization: ' . $authHeaderContent,
        'Date: ' . $requestDate
    ];

    $httpResponse = $httpClient->request(
        'POST',
        '',
        $xml,
        $headers
    );

    if ($httpResponse->status >= 400) {
        echo 'ERROR RESPONSE' . "\n";
    }
    echo $httpResponse->body;
}

function generateNonce($requestDate, $body, $key)
{
    return hash_hmac('sha512', $requestDate . "\n" . $body, $key);
}

spl_autoload_register(function ($class) {
    if (strpos($class, 'Bepado\\SDK') === 0) {
        $file = __DIR__ . '/../main/' . str_replace('\\', '/', $class) . '.php';
        require_once($file);
    }
});

if (count($argv) <= 3) {
    echo "php peakProducts.php shopUrl apiKey productid...\n";
    exit(1);
}

peakProducts($argv[1], $argv[2], array_slice($argv, 3));
