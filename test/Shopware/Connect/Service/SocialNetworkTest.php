<?php

namespace Shopware\Connect\Service;

use Shopware\Connect\HttpClient\Response;
use Shopware\Connect\Struct\Order;
use Shopware\Connect\Struct\Shipping;

class SocialNetworkTest extends \PHPUnit_Framework_TestCase
{
    const APIKEY = 'abcdefg';

    public function testUpdateOrderStatus()
    {
        $status = new \Shopware\Connect\Struct\OrderStatus(array(
            'id' => 'abcdefg',
            'status' => \Shopware\Connect\Struct\OrderStatus::STATE_OPEN,
        ));
        $client = \Phake::mock('Shopware\Connect\HttpClient');

        \Phake::when($client)->request(
            'POST',
            '/sdk/update-order-status',
            json_encode($status),
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Shop: 1',
                'X-Shopware-Connect-Key: ' . hash_hmac('sha512', json_encode($status), self::APIKEY)
            )
        )->thenReturn(new Response(array('status' => 200)));

        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->updateOrderStatus($status);
    }

    public function testUnsubscribeProducts()
    {
        $productIds = array(
            new \Shopware\Connect\Struct\ProductId(array(
                'shopId' => 10,
                'sourceId' => 'foo',
            )),
            new \Shopware\Connect\Struct\ProductId(array(
                'shopId' => 20,
                'sourceId' => 'bar',
            )),
        );

        $client = \Phake::mock('Shopware\Connect\HttpClient');

        \Phake::when($client)->request(
            'POST',
            '/sdk/unsubscribe-products',
            json_encode($productIds),
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Shop: 1',
                'X-Shopware-Connect-Key: ' . hash_hmac('sha512', json_encode($productIds), self::APIKEY)
            )
        )->thenReturn(new Response(array('status' => 200)));

        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->unsubscribeProducts($productIds);
    }

    public function testCalculateFinishTime()
    {
        $changes = 300;
        $json = json_encode(array('count' => $changes));
        $client = \Phake::mock('Shopware\Connect\HttpClient');

        \Phake::when($client)->request(
            'POST',
            '/sdk/calculate-finish-time',
            $json,
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Shop: 1',
                'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $json, self::APIKEY)
            )
        )->thenReturn(new Response(array('status' => 200, 'body' => '{"ok": true, "time": 900}')));

        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->calculateFinishTime($changes);
    }


    public function testGetMarketplaceProductAttributes()
    {
        $content = json_encode(array());
        $client = \Phake::mock('Shopware\Connect\HttpClient');

        \Phake::when($client)->request(
            'POST',
            '/sdk/marketplace/attributes',
            $content,
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Shop: 1',
                'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $content, self::APIKEY)
            )
        )->thenReturn(new Response(array('status' => 200, 'body' => '{"ok": true, "attributes": [{"energy_saving_information": "Energiesparinformationen"}]}')));

        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->getMarketplaceProductAttributes();
    }

    public function testGetMarketplaceSettings()
    {
        $content = json_encode(array());
        $client = \Phake::mock('Shopware\Connect\HttpClient');

        \Phake::when($client)->request(
            'POST',
            '/sdk/marketplace/settings',
            $content,
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Shop: 1',
                'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $content, self::APIKEY)
            )
        )->thenReturn(new Response(array('status' => 200, 'body' => '{"ok": true, "settings": [{"marketplaceName": "Shopware Enterprise Marketplace"}]}')));

        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $socialNetwork->getMarketplaceSettings();
    }

    public function testCalculateShippingCosts()
    {
        $order = new Order();
        $payload = json_encode(array('order' => $order));

        $client = \Phake::mock('Shopware\Connect\HttpClient');

        \Phake::when($client)->request(
            'POST',
            '/sdk/shipping-costs',
            $payload,
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Shop: 1',
                'X-Shopware-Connect-Key: ' . hash_hmac('sha512', $payload, self::APIKEY)
            )
        )->thenReturn(new Response(array('status' => 200, 'body' => $this->getShippingResponse())));

        $dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');

        $socialNetwork = new SocialNetwork($client, $dispatcher, 1, self::APIKEY);
        $shipping = $socialNetwork->calculateShippingCosts($order);

        $this->assertInstanceOf('Shopware\\Connect\\Struct\\Shipping', $shipping);
    }

    private function getShippingResponse()
    {
        $shipping = new Shipping();
        $shipping->deliveryWorkDays = 3;
        $shipping->shippingCosts = 10;
        $shipping->grossShippingCosts = 10 * (1 + 0.19);
        $shipping->isShippable = true;

        return json_encode(array(
            'ok' => true,
            'shipping' => $shipping
        ));
    }
}
