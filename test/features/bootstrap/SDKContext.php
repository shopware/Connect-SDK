<?php

namespace Shopware\Connect;

use Behat\Behat\Context\BehatContext;

use Shopware\Connect\ProductFromShop\Test;
use Shopware\Connect\Rpc;
use Shopware\Connect\Struct;
use Shopware\Connect\Struct\Product;
use Shopware\Connect\Service\PurchasePriceSecurity;

require_once __DIR__ . '/ShopGateway/DirectAccess.php';
require_once __DIR__ . '/ShopFactory/DirectAccess.php';
require_once __DIR__ . '/Logger/Test.php';

/**
 * Base SDK features context.
 */
class SDKContext extends BehatContext
{
    const APIKEY = 'apikey';
    const OFFER_VALID_UNTIL = 2067855023; // 12.07.2035 12:10

    /**
     * SDK entry point
     *
     * @var SDK
     */
    protected $sdk;

    /**
     * SDK dependencies for optional direct access
     *
     * @var DependencyResolver
     */
    protected $dependencies;

    /**
     * Currently used mock for to shop gateway
     *
     * @var ProductToShop
     */
    protected $productToShop;

    /**
     * Currently used mock for from shop gateway
     *
     * @var ProductFromShop
     */
    protected $productFromShop;

    /**
     * Main gateway of the local shop
     *
     * @var Gateway
     */
    protected $gateway;

    protected function getGateway($connection)
    {
        $storage = getenv('STORAGE') ?: 'InMemory';
        switch ($storage) {
            case 'InMemory':
                $gateway = new Gateway\InMemory();
                break;
            case 'MySQLi':
                $gateway = new Gateway\MySQLi($connection);
                break;
            case 'PDO':
                $gateway = new Gateway\PDO($connection);
                break;
            default:
                throw new \RuntimeException("Unknown storage backend $storage");
        }

        $gateway->setShopId('shop');
        $gateway->setConfig('_price_type', SDK::PRICE_TYPE_BOTH);

        return $gateway;
    }

    public function initSDK($connection)
    {
        $mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
        $this->productToShop = $mockGenerator->getMock(ProductToShop::class);
        $this->productFromShop = $mockGenerator->getMockForAbstractClass(
            Test::class
        );

        $this->sdk = new SDK(
            self::APIKEY,
            'http://example.com/endpoint',
            $this->gateway = $this->getGateway($connection),
            $this->productToShop,
            $this->productFromShop,
            null,
            new \Shopware\Connect\HttpClient\NoSecurityRequestSigner()
        );

        $dependenciesProperty = new \ReflectionProperty($this->sdk, 'dependencies');
        $dependenciesProperty->setAccessible(true);
        $this->dependencies = $dependenciesProperty->getValue($this->sdk);
    }

    /**
     * Get fake product for ID
     *
     * @param int $productId
     * @return Product
     */
    protected function getProduct($productId, $data = 'foo')
    {
        return new Product(
            array(
                'sourceId' => (string) $productId,
                'title' => $data,
                'price' => $productId * .89,
                'purchasePrice' => $productId * .89,
                'purchasePriceHash' => $this->purchasePriceHash($productId * .89),
                'offerValidUntil' => self::OFFER_VALID_UNTIL,
                'currency' => 'EUR',
                'availability' => $productId,
                'categories' => array('/others'),
                'vendor' => 'Something',
            )
        );
    }

    /**
     * @param float $price
     * @param string $apiKey
     * @return string
     */
    protected function purchasePriceHash($price, $apiKey = null)
    {
        return PurchasePriceSecurity::hash($price, self::OFFER_VALID_UNTIL, $apiKey ?: self::APIKEY);
    }

    /**
     * Make a RPC call using the marshalling and unmarshalling
     *
     * @param Struct\RpcCall $rpcCall
     * @return mixed
     */
    protected function makeRpcCall(Struct\RpcCall $rpcCall)
    {
        $result = $this->dependencies->getUnmarshaller()->unmarshal(
            $this->sdk->handle(
                $this->dependencies->getMarshaller()->marshal($rpcCall)
            )
        );

        return $result->arguments[0]->result;
    }
}
