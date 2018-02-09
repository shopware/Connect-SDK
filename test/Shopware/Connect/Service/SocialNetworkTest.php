<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\HttpClient;
use Shopware\Connect\HttpClient\Response;
use Shopware\Connect\Struct\Order;
use Shopware\Connect\Struct\Shipping;
use Shopware\Connect\Struct\VerificatorDispatcher;

class SocialNetworkTest extends \PHPUnit_Framework_TestCase
{
    const APIKEY = 'abcdefg';

    public function testUpdateOrderStatus()
    {
        $status = new \Shopware\Connect\Struct\OrderStatus([
            'id' => 'abcdefg',
            'status' => \Shopware\Connect\Struct\OrderStatus::STATE_OPEN,
        ]);
        $client = $this->createMock(HttpClient::class);

        $client->method('request')
            ->with(
                'POST',
                '/sdk/update-order-status',
                json_encode($status),
                [
                    'Content-Type: application/json',
                    'X-Shopware-Connect-Shop: 1',
                    'X-Shopware-Connect-Key: ' . hash_hmac('sha512', json_encode($status), self::APIKEY)
                ]
            )->willReturn(new Response(['status' => 200]));

        $dispatcher = $this->createMock(VerificatorDispatcher::class);

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->updateOrderStatus($status);
    }

    public function testUnsubscribeProducts()
    {
        $productIds = [
            new \Shopware\Connect\Struct\ProductId([
                'shopId' => 10,
                'sourceId' => 'foo',
            ]),
            new \Shopware\Connect\Struct\ProductId([
                'shopId' => 20,
                'sourceId' => 'bar',
            ]),
        ];

        $client = $this->createMock(HttpClient::class);

        $client->method('request')
            ->with(
                'POST',
                '/sdk/unsubscribe-products',
                json_encode($productIds),
                [
                    'Content-Type: application/json',
                    'X-Shopware-Connect-Shop: 1',
                    'X-Shopware-Connect-Key: ' . hash_hmac('sha512', json_encode($productIds), self::APIKEY)
                ]
            )->willReturn(new Response(['status' => 200]));

        $dispatcher = $this->createMock(VerificatorDispatcher::class);

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->unsubscribeProducts($productIds);
    }

    public function testCalculateFinishTime()
    {
        $changes = 300;
        $json = json_encode(['count' => $changes]);
        $client = $this->createMock(HttpClient::class);

        $client->method('request')
            ->with(
                'POST',
                '/sdk/calculate-finish-time',
                $json,
                [
                    'Content-Type: application/json',
                    'X-Shopware-Connect-Shop: 1',
                    'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $json, self::APIKEY)
                ]
            )->willReturn(new Response(['status' => 200, 'body' => '{"ok": true, "time": 900}']));

        $dispatcher = $this->createMock(VerificatorDispatcher::class);

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->calculateFinishTime($changes);
    }

    public function testGetMarketplaceProductAttributes()
    {
        $content = json_encode([]);
        $client = $this->createMock(HttpClient::class);

        $client->method('request')
            ->with(
                'POST',
                '/sdk/marketplace/attributes',
                $content,
                [
                    'Content-Type: application/json',
                    'X-Shopware-Connect-Shop: 1',
                    'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $content, self::APIKEY)
                ]
            )->willReturn(new Response(['status' => 200, 'body' => '{"ok": true, "attributes": [{"energy_saving_information": "Energiesparinformationen"}]}']));

        $dispatcher = $this->createMock(VerificatorDispatcher::class);

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->getMarketplaceProductAttributes();
    }

    public function testGetMarketplaceSettings()
    {
        $content = json_encode([]);
        $client = $this->createMock(HttpClient::class);

        $client->method('request')
            ->with(
                'POST',
                '/sdk/marketplace/settings',
                $content,
                [
                    'Content-Type: application/json',
                    'X-Shopware-Connect-Shop: 1',
                    'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $content, self::APIKEY)
                ]
            )->willReturn(new Response(['status' => 200, 'body' => '{"ok": true, "settings": [{"marketplaceName": "Shopware Enterprise Marketplace"}]}']));

        $dispatcher = $this->createMock(VerificatorDispatcher::class);

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->getMarketplaceSettings();
    }

    public function testCalculateShippingCosts()
    {
        $order = new Order();
        $payload = json_encode(['order' => $order]);

        $client = $this->createMock(HttpClient::class);

        $client->method('request')
            ->with(
                'POST',
                '/sdk/shipping-costs',
                $payload,
                [
                    'Content-Type: application/json',
                    'X-Shopware-Connect-Shop: 1',
                    'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $payload, self::APIKEY)
                ]
            )->willReturn(new Response(['status' => 200, 'body' => $this->getShippingResponse()]));

        $dispatcher = $this->createMock(VerificatorDispatcher::class);

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $shipping = $socialNetwork->calculateShippingCosts($order);

        self::assertInstanceOf(Shipping::class, $shipping);
    }

    private function getShippingResponse()
    {
        $shipping = new Shipping();
        $shipping->deliveryWorkDays = 3;
        $shipping->shippingCosts = 10;
        $shipping->grossShippingCosts = 10 * (1 + 0.19);
        $shipping->isShippable = true;

        return json_encode([
            'ok' => true,
            'shipping' => $shipping
        ]);
    }
}
