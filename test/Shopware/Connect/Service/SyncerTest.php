<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Service;

use Shopware\Connect\Struct\RpcCall;
use Shopware\Connect;
use Shopware\Connect\Struct\Change\FromShop\Delete;
use Shopware\Connect\Struct\Change\FromShop\Insert;
use Shopware\Connect\Struct\Change\FromShop\Update;
use Shopware\Connect\HttpClient\NoSecurityRequestSigner;

abstract class SyncerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Connect\SDK
     */
    protected $sdk;

    /**
     * Dependency Resolver
     *
     * @var DependencyResolver
     */
    protected $dependencies;

    /**
     * Get used gateway for test
     *
     * @return SDK\Gateway
     */
    abstract protected function getGateway();

    /**
     * Get SDK
     *
     * @param SDK\ProductFromShop $productFromShop
     * @return SDK
     */
    protected function getSDK(Connect\ProductFromShop $productFromShop)
    {
        $gateway = $this->getGateway();
        $gateway->setShopId('shop');

        $this->sdk = new Connect\SDK(
            'apikey',
            'http://example.com/endpoint',
            $gateway,
            $this->getMock('\\Shopware\\Connect\\ProductToShop'),
            $productFromShop,
            null,
            new NoSecurityRequestSigner()
        );

        $dependenciesProperty = new \ReflectionProperty($this->sdk, 'dependencies');
        $dependenciesProperty->setAccessible(true);
        $this->dependencies = $dependenciesProperty->getValue($this->sdk);

        return $this->sdk;
    }

    /**
     * Make a RPC call using the marshalling and unmarshalling
     *
     * @param RpcCall $rpcCall
     * @return mixed
     */
    protected function makeRpcCall(RpcCall $rpcCall)
    {
        $result = $this->dependencies->getUnmarshaller()->unmarshal(
            $this->sdk->handle(
                $this->dependencies->getMarshaller()->marshal($rpcCall)
            )
        );

        return $result->arguments[0]->result;
    }

    /**
     * Get product provider
     *
     * Returns a set of products, as defined by the given array.
     *
     * The "data" can be changed, to cause different product hashes.
     *
     * @param array $products
     * @param string $data
     * @return \Shopware\Connect\ProductFromShop
     */
    protected function getProductFromShop(array $products, $data = 'foo')
    {
        $products = array_map('strval', $products);
        $provider = $this->getMock('\\Shopware\\Connect\\ProductFromShop');
        $provider
            ->expects($this->any())
            ->method('getExportedProductIDs')
            ->will($this->returnValue($products));
        $provider
            ->expects($this->any())
            ->method('getProducts')
            ->will(
                $this->returnValue(
                    array_map(
                        function ($productId) use ($data) {
                            return SyncerTest::getProduct($productId, $data);
                        },
                        $products
                    )
                )
            );

        return $provider;
    }

    /**
     * Get fake product for ID
     *
     * @param int $productId
     * @param string $data
     * @return \Shopware\Connect\Struct\Product
     */
    public static function getProduct($productId, $data = 'foo')
    {
        return new Connect\Struct\Product(
            array(
                'shopId' => 'shop-1',
                'sourceId' => (string) $productId,
                'title' => $data,
                'vendor' => 'Foo',
                'price' => $productId * .89,
                'purchasePrice' => $productId * .89,
                'currency' => 'EUR',
                'availability' => $productId,
                'categories' => array('/others'),
            )
        );
    }

    /**
     * Assert changes are exposed as expected
     *
     * @param array $expectation
     * @param array $changes
     * @return void
     */
    protected function assertChanges($expectation, $changes)
    {
        $dependencies = $this->dependencies;
        $this->assertEquals(
            $expectation,
            array_map(
                function ($change) use ($dependencies) {
                    $dependencies->getVerificator()->verify($change);

                    // We do not care to comapre revision and product in change
                    $change = clone $change;
                    $change->revision = null;
                    if (isset($change->product)) {
                        $change->product = null;
                    }
                    return $change;
                },
                $changes
            )
        );
    }

    public function testInitialBuild()
    {
        $sdk = $this->getSdk($this->getProductFromShop(array(1, 2)));
        $sdk->recreateChangesFeed();

        $this->assertChanges(
            array(
                new Insert(array('sourceId' => '1')),
                new Insert(array('sourceId' => '2')),
            ),
            $changes = $this->makeRpcCall(
                new RpcCall(
                    array(
                        'service' => 'products',
                        'command' => 'fromShop',
                        'arguments' => array(null, 100),
                    )
                )
            )
        );
        return end($changes)->revision;
    }

    /**
     * @depends testInitialBuild
     */
    public function testReIndex()
    {
        $revision = $this->testInitialBuild();
        $sdk = $this->getSdk($this->getProductFromShop(array(1, 2)));
        $sdk->recreateChangesFeed();

        $this->assertChanges(
            array(),
            $this->makeRpcCall(
                new RpcCall(
                    array(
                        'service' => 'products',
                        'command' => 'fromShop',
                        'arguments' => array($revision, 100),
                    )
                )
            )
        );
    }

    /**
     * @depends testReIndex
     */
    public function testProductUpdate()
    {
        $revision = $this->testInitialBuild();
        $sdk = $this->getSdk($this->getProductFromShop(array(1, 2), 'update'));
        $sdk->recreateChangesFeed();

        $this->assertChanges(
            array(
                new Update(array('sourceId' => '1')),
                new Update(array('sourceId' => '2')),
            ),
            $this->makeRpcCall(
                new RpcCall(
                    array(
                        'service' => 'products',
                        'command' => 'fromShop',
                        'arguments' => array($revision, 100),
                    )
                )
            )
        );
    }

    /**
     * @depends testProductUpdate
     */
    public function testReFetchChanges()
    {
        $revision = $this->testInitialBuild();
        $sdk = $this->getSdk($this->getProductFromShop(array(1, 2), 'update'));
        $sdk->recreateChangesFeed();

        $this->makeRpcCall(
            new RpcCall(
                array(
                    'service' => 'products',
                    'command' => 'fromShop',
                    'arguments' => array($revision, 100),
                )
            )
        );

        $this->assertChanges(
            array(
                new Update(array('sourceId' => '1')),
                new Update(array('sourceId' => '2')),
            ),
            $this->makeRpcCall(
                new RpcCall(
                    array(
                        'service' => 'products',
                        'command' => 'fromShop',
                        'arguments' => array($revision, 100),
                    )
                )
            )
        );
    }

    /**
     * @depends testReIndex
     */
    public function testProductDelete()
    {
        $revision = $this->testInitialBuild();
        $sdk = $this->getSdk($this->getProductFromShop(array()));
        $sdk->recreateChangesFeed();

        $this->assertChanges(
            array(
                new Delete(array('sourceId' => '1')),
                new Delete(array('sourceId' => '2')),
            ),
            $this->makeRpcCall(
                new RpcCall(
                    array(
                        'service' => 'products',
                        'command' => 'fromShop',
                        'arguments' => array($revision, 100),
                    )
                )
            )
        );
    }

    public function testFeatures()
    {
        $features = array(
            'sellNotInStock' => true
        );
        $this->getGateway()->setFeatures($features);

        $result = $this->getGateway()->getFeatures();
        $this->assertEquals($features, $result);
    }

    public function testFeatureIsNotEnabled()
    {
        $this->assertFalse($this->getGateway()->isFeatureEnabled('dummyFeature'));
    }
}
