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
            ->setProductToShop(\Phake::mock('Shopware\Connect\ProductToShop'))
            ->setProductFromShop(\Phake::mock('Shopware\Connect\ProductFromShop'))
            ->setErrorHandler(\Phake::mock('Shopware\Connect\ErrorHandler'))
            ->setPluginSoftwareVersion('Foo')
        ;

        $sdk = $builder->build();

        $this->assertInstanceOf('Shopware\Connect\SDK', $sdk);
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
            ->setProductToShop(\Phake::mock('Shopware\Connect\ProductToShop'))
            ->setProductFromShop(\Phake::mock('Shopware\Connect\ProductFromShop'))
        ;

        $sdk = $builder->build();

        $this->assertInstanceOf('Shopware\Connect\SDK', $sdk);
    }

    public function testBuildSdkMissingArgumentsThrowsException()
    {
        $builder = new \Shopware\Connect\SDKBuilder();

        $this->expectException(\RuntimeException::class);
        $builder->build();
    }
}
