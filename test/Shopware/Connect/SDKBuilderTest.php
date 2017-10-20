<?php

namespace Shopware\Connect;

use PDO;

class SDKBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildSdkWithAllParameters()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Test requires PDO and PDO_SQLITE.');
        }

        $builder = new \Shopware\Connect\SDKBuilder();
        $builder
            ->setApiKey('foo')
            ->setApiEndpointUrl('http://foo/bar')
            ->configurePDOGateway(new PDO('sqlite::memory:'))
            ->setProductToShop($this->createMock(ProductToShop::class))
            ->setProductFromShop($this->createMock(ProductFromShop::class))
            ->setErrorHandler($this->createMock(ErrorHandler::class))
            ->setPluginSoftwareVersion('Foo')
        ;

        $sdk = $builder->build();

        $this->assertInstanceOf(SDK::class, $sdk);
    }

    public function testBuildSdkWithRequiredOnly()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Test requires PDO and PDO_SQLITE.');
        }

        $builder = new \Shopware\Connect\SDKBuilder();
        $builder
            ->setApiKey('foo')
            ->setApiEndpointUrl('http://foo/bar')
            ->configurePDOGateway(new PDO('sqlite::memory:'))
            ->setProductToShop($this->createMock(ProductToShop::class))
            ->setProductFromShop($this->createMock(ProductFromShop::class))
        ;

        $sdk = $builder->build();

        $this->assertInstanceOf(SDK::class, $sdk);
    }

    public function testBuildSdkMissingArgumentsThrowsException()
    {
        $builder = new \Shopware\Connect\SDKBuilder();

        $this->expectException(\RuntimeException::class);
        $builder->build();
    }
}
