<?php

namespace Shopware\Connect\Rpc;

class LegacyOrderMarshallingTest extends \PHPUnit_Framework_TestCase
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
            ['RpcCalls/Order', 'MarshalledXml/Order'],
        ];
    }

    /**
     * @param string $rpcCall
     * @param string $xml
     * @dataProvider provideMarshalData
     */
    public function testMarshalToLegacyOrder($rpcCall, $xml)
    {
        $marshaller = new \Shopware\Connect\Rpc\Marshaller\CallMarshaller\XmlCallMarshaller(
            new \Shopware\Connect\XmlHelper(),
            new \Shopware\Connect\Rpc\Marshaller\Converter\LegacyOrderConverter()
        );

        $result = $marshaller->marshal(
            include "{$this->fixturesDirectory}/{$rpcCall}.php"
        );

        $expected = file_get_contents(
            "{$this->expectationsDirectory}/{$xml}.xml"
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Provide sets of RpcCalls and marshalled XML structs
     *
     * @return array
     */
    public function provideUnmarshalData()
    {
        return [
            ['MarshalledXml/Order', 'RpcCalls/Order'],
        ];
    }

    /**
     * @param string $rpcCall
     * @param string $xml
     * @dataProvider provideUnmarshalData
     */
    public function testUnmarshalFromLegacyOrder($xml, $rpcCall)
    {
        $unmarshaller = new \Shopware\Connect\Rpc\Marshaller\CallUnmarshaller\XmlCallUnmarshaller();

        $result = $unmarshaller->unmarshal(
            file_get_contents("{$this->fixturesDirectory}/{$xml}.xml")
        );

        $expected = include "{$this->expectationsDirectory}/{$rpcCall}.php";

        $this->assertEquals($expected, $result);
    }
}
