<?php

namespace Shopware\Connect\ShopGateway;

use Shopware\Connect\HttpClient;
use Shopware\Connect\Rpc\Marshaller;
use Shopware\Connect\HttpClient\Response;
use Shopware\Connect\Struct;
use Shopware\Connect\XmlHelper;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get invalid response
     *
     * @return array
     */
    public function getInvalidResponse()
    {
        return [
            [404, '<html><body><h1>Not Found</h1></body></html>'],
            [500, '<html><body><h1>Internal Server Error</h1></body></html>'],
            [200, 'Foo'],
            [200, '<?xml ?><foo></bar>'],
            // Oxid endpoints like to answer with an empty reponse
            [200, ''],
            // Oxid endpoints mike redirect to the frontpage including HTML on exception
            [200, '<html></html>'],
        ];
    }

    /**
     * @dataProvider getInvalidResponse
     */
    public function testFailOnInvalidResponse($code, $body)
    {
        $this->expectException(\RuntimeException::class);
        $httpClient = $this->createMock(HttpClient::class);
        $httpClient
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(new Response([
                'status' => $code,
                'headers' => [],
                'body' => $body,
            ])));

        $requestSigner = $this->getMockBuilder(ShopRequestSigner::class)
                              ->setMethods(['signRequest'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $requestSigner
            ->expects($this->any())
            ->method('signRequest')
            ->will($this->returnValue([]));

        $xmlHelper = new XmlHelper();
        $shopGateway = new Http(
            $httpClient,
            new Marshaller\CallMarshaller\XmlCallMarshaller($xmlHelper),
            new Marshaller\CallUnmarshaller\XmlCallUnmarshaller(new Marshaller\Converter\NoopConverter(), $xmlHelper),
            $requestSigner
        );

        $shopGateway->checkProducts(
            new Struct\Order(),
            1
        );
    }
}
