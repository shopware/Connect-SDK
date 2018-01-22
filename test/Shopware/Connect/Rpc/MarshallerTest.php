<?php

namespace Shopware\Connect\Rpc;

use Shopware\Connect\Struct;

class MarshallerTest extends \PHPUnit_Framework_TestCase
{
    private $fixturesDirectory;

    private $expectationsDirectory;

    protected function setUp()
    {
        parent::setUp();

        $this->fixturesDirectory = __DIR__ . '/../../../_fixtures';
        $this->expectationsDirectory = __DIR__ . '/../../../_expectations';
    }

    /**
     * Provide sets of RpcCalls and marshalled XML structs
     *
     * @return array
     */
    public function provideMarshalData()
    {
        return [
            ['RpcCalls/Empty', 'MarshalledXml/Empty'],
            ['RpcCalls/Integer', 'MarshalledXml/Integer'],
            ['RpcCalls/Boolean', 'MarshalledXml/Boolean'],
            ['RpcCalls/Null', 'MarshalledXml/Null'],
            ['RpcCalls/Float', 'MarshalledXml/Float'],
            ['RpcCalls/String', 'MarshalledXml/String'],
            ['RpcCalls/EmptyArray', 'MarshalledXml/EmptyArray'],
            ['RpcCalls/Array', 'MarshalledXml/Array'],
            ['RpcCalls/Product', 'MarshalledXml/Product'],
            ['RpcCalls/Mixed', 'MarshalledXml/Mixed'],
        ];
    }

    /**
     * @param string $rpcCall
     * @param string $xml
     * @dataProvider provideMarshalData
     */
    public function testMarshalRpcCall($rpcCall, $xml)
    {
        $marshaller = new \Shopware\Connect\Rpc\Marshaller\CallMarshaller\XmlCallMarshaller(
            new \Shopware\Connect\XmlHelper()
        );

        $this->assertMarshallEquals($marshaller, $rpcCall, $xml);
    }

    public function provideSimpleClassMapMarshalData()
    {
        return [
            ['RpcCalls/Product', 'MarshalledXml/SimpleClassMapProduct'],
            ['RpcCalls/Mixed', 'MarshalledXml/SimpleClassMapMixed'],
        ];
    }

    /**
     * @param $rpcCall
     * @param $xml
     * @dataProvider provideSimpleClassMapMarshalData
     */
    public function testMarshalObjectWithSimpleClassMapConversion($rpcCall, $xml)
    {
        $marshaller = new \Shopware\Connect\Rpc\Marshaller\CallMarshaller\XmlCallMarshaller(
            new \Shopware\Connect\XmlHelper(),
            new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter(
                [
                    'Shopware\\Connect\\Rpc\\ShopProduct' => 'Shopware\\Connect\\Struct\\Product'
                ]
            )
        );

        $this->assertMarshallEquals($marshaller, $rpcCall, $xml);
    }

    public function provideChainingConverterMarshalData()
    {
        return [
            // Intentionally simple results, since these should not change!
            ['RpcCalls/Product', 'MarshalledXml/SimpleClassMapProduct'],
            ['RpcCalls/Mixed', 'MarshalledXml/SimpleClassMapMixed'],
            // Specialized
            ['RpcCalls/Exception', 'MarshalledXml/ChainedMapException'],
            ['RpcCalls/InvalidArgumentException', 'MarshalledXml/ChainedMapInvalidArgumentException'],
        ];
    }

    /**
     * @param $rpcCall
     * @param $xml
     * @dataProvider provideChainingConverterMarshalData
     */
    public function testMarshalExceptionsChainingConversion($rpcCall, $xml)
    {
        $marshaller = new \Shopware\Connect\Rpc\Marshaller\CallMarshaller\XmlCallMarshaller(
            new \Shopware\Connect\XmlHelper(),
            new \Shopware\Connect\Rpc\Marshaller\Converter\ChainingConverter(
                [
                    new \Shopware\Connect\Rpc\Marshaller\Converter\ExceptionToErrorConverter(),
                    new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter(
                        [
                            'Shopware\\Connect\\Rpc\\ShopProduct' => 'Shopware\\Connect\\Struct\\Product'
                        ]
                    )
                ]
            )
        );

        $this->assertMarshallEquals($marshaller, $rpcCall, $xml);
    }

    /**
     * Provide sets of marshalled XML and RpcCall structs
     *
     * @return array
     */
    public function provideUnmarshalData()
    {
        return [
            ['MarshalledXml/Empty', 'RpcCalls/Empty'],
            ['MarshalledXml/Integer', 'RpcCalls/Integer'],
            ['MarshalledXml/Boolean', 'RpcCalls/Boolean'],
            ['MarshalledXml/Null', 'RpcCalls/Null'],
            ['MarshalledXml/Float', 'RpcCalls/Float'],
            ['MarshalledXml/String', 'RpcCalls/String'],
            ['MarshalledXml/EmptyArray', 'RpcCalls/EmptyArray'],
            ['MarshalledXml/Array', 'RpcCalls/Array'],
            ['MarshalledXml/ArrayWithoutKey', 'RpcCalls/Array'],
            ['MarshalledXml/Product', 'RpcCalls/Product'],
            ['MarshalledXml/Mixed', 'RpcCalls/Mixed'],
        ];
    }

    /**
     * @param string $xml
     * @param string $rpcCall
     * @dataProvider provideUnmarshalData
     */
    public function testUnmarshalRpcCall($xml, $rpcCall)
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller();

        $this->assertUnmarshallEquals($unmarshaller, $rpcCall, $xml);
    }

    public function provideSimpleClassMapUnmarshalData()
    {
        return [
            ['MarshalledXml/Product', 'RpcCalls/SimpleClassMapProduct'],
            ['MarshalledXml/Mixed', 'RpcCalls/SimpleClassMapMixed'],
        ];
    }

    /**
     * @param string $xml
     * @param string $rpcCall
     * @dataProvider provideSimpleClassMapUnmarshalData
     */
    public function testUnmarshalObjectWithSimpleClassMapConversion($xml, $rpcCall)
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller(
            new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter(
                [
                    'Shopware\\Connect\\Struct\\Product' => 'Shopware\\Connect\\Rpc\\ShopProduct'
                ]
            )
        );

        $this->assertUnmarshallEquals($unmarshaller, $rpcCall, $xml);
    }

    public function provideErrorUnmarshalData()
    {
        return [
            ['MarshalledXml/Exception'],
            ['MarshalledXml/InvalidArgumentException'],
        ];
    }

    /**
     * @param string $xml
     * @param string $rpcCall
     * @dataProvider provideErrorUnmarshalData
     * @expectedException \Exception
     */
    public function testUnmarshalError($xml)
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller(
            new \Shopware\Connect\Rpc\Marshaller\Converter\ChainingConverter(
                [
                    new \Shopware\Connect\Rpc\Marshaller\Converter\ErrorToExceptionConverter(),
                    new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter(
                        [
                            'Shopware\\Connect\\Struct\\Product' => 'Shopware\\Connect\\Rpc\\Product\\ShopProduct'
                        ]
                    )
                ]
            )
        );

        $result = $unmarshaller->unmarshal(
            file_get_contents(
                "{$this->fixturesDirectory}/{$xml}.xml"
            )
        );
    }

    public function testUnmarshallIgnoreNonExistantProperties()
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller(
            new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter(
                [
                    'Shopware\\Connect\\Struct\\Product' => 'Shopware\\Connect\\Rpc\\TestSmallProduct'
                ]
            )
        );

        $result = $unmarshaller->unmarshal(
            file_get_contents(
                "{$this->fixturesDirectory}/MarshalledXml/Product.xml"
            )
        );

        $this->assertInstanceOf('Shopware\Connect\Rpc\TestSmallProduct', $result->arguments[0]);
        $this->assertEquals('Secret pH Balanced INVISIBLE Solid powder fresh', $result->arguments[0]->title);
        $this->assertEquals('4.99', $result->arguments[0]->price);
    }

    public function testUnmarshallNonStructClassThrowsException()
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller(
            new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter(
                [
                    'Shopware\\Connect\\Struct\\Product' => 'Shopware\\Connect\\Rpc\\TestSmallProduct'
                ]
            )
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot unmarshall non-Struct classes such as PDO');

        $unmarshaller->unmarshal(
            file_get_contents(
                "{$this->fixturesDirectory}/MarshalledXml/InvalidStruct.xml"
            )
        );
    }

    /**
     * @group BEP-534
     */
    public function testInvalidBomsAreStripped()
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller(
            new \Shopware\Connect\Rpc\Marshaller\Converter\SimpleClassMapConverter([])
        );

        $data = $unmarshaller->unmarshal(
            file_get_contents(
                "{$this->fixturesDirectory}/customer_error1.xml"
            )
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\RpcCall', $data);
    }

    private function assertMarshallEquals($marshaller, $rpcCall, $xml)
    {
        $result = $marshaller->marshal(
            include "{$this->fixturesDirectory}/{$rpcCall}.php"
        );

        if (isset($_ENV['__TEST_GENERATE_FIXTURE'])) {
            file_put_contents(
                "{$this->expectationsDirectory}/{$xml}.xml",
                $result
            );
        }

        $expected = file_get_contents(
            "{$this->expectationsDirectory}/{$xml}.xml"
        );

        $this->assertEquals($expected, $result);
    }

    private function assertUnmarshallEquals($unmarshaller, $rpcCall, $xml)
    {
        $result = $unmarshaller->unmarshal(
            file_get_contents(
                "{$this->fixturesDirectory}/{$xml}.xml"
            )
        );

        $expected = include "{$this->expectationsDirectory}/{$rpcCall}.php";

        $this->assertEquals($expected, $result);
    }
}

class TestSmallProduct
{
    public $title;
    public $price;
}

class ShopProduct extends Struct
{
    public $shopId;
    public $sourceId;
    public $sku;
    public $groupId;
    public $masterId;
    public $approved = false;
    public $purchasePrice;
    public $fixedPrice;
    public $grossMarginPercent;
    public $freeDelivery = false;
    public $deliveryDate;
    public $relevance = 0;
    public $productId;
    public $revisionId;
    public $language = 'de';
    public $ean;
    public $url;
    public $title;
    public $shortDescription;
    public $longDescription;
    public $additionalDescription;
    public $vendor;
    public $vat = 0.19;
    public $price;
    public $currency;
    public $availability;
    public $images = [];
    public $categories = [];
    public $properties = [];
    public $attributes = [];
    public $variant = [];
    public $translations = [];
    public $minPurchaseQuantity = 1;
    public $configuratorSetType;
}
