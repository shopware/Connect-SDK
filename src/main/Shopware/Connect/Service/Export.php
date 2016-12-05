<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Service;

use Shopware\Connect\ProductFromShop;
use Shopware\Connect\Struct\Change\FromShop\MakeMainVariant;
use Shopware\Connect\Struct\PaymentStatus as PaymentStatusStruct;
use Shopware\Connect\Struct\VerificatorDispatcher;
use Shopware\Connect\Gateway;
use Shopware\Connect\ProductHasher;
use Shopware\Connect\RevisionProvider;

/**
 * Service reponsible for exporting product data to bepado.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Export
{
    private $fromShop;
    private $verificator;
    private $gateway;
    private $productHasher;
    private $revisionProvider;

    public function __construct(
        ProductFromShop $fromShop,
        VerificatorDispatcher $verificator,
        Gateway $gateway,
        ProductHasher $productHasher,
        RevisionProvider $revisionProvider
    )
    {
        $this->fromShop = $fromShop;
        $this->verificator = $verificator;
        $this->gateway = $gateway;
        $this->productHasher = $productHasher;
        $this->revisionProvider = $revisionProvider;
    }

    /**
     * Record product insert
     *
     * Establish a hook in your shop and call this method for every new
     * product, which should be exported to Shopware-Connect.
     *
     * @param string $productId
     * @return void
     */
    public function recordInsert($productId)
    {
        $product = $this->getProduct($productId);
        $product->shopId = $this->gateway->getShopId();

        $this->verificator->verify($product, $this->verificationGroups());
        $this->gateway->recordInsert(
            $product->sourceId,
            $this->productHasher->hash($product),
            $this->revisionProvider->next(),
            $product
        );
    }

    /**
     * Record product update
     *
     * Establish a hook in your shop and call this method for every update of a
     * product, which is exported to Shopware-Connect.
     *
     * @param string $productId
     * @return void
     */
    public function recordUpdate($productId)
    {
        $product = $this->getProduct($productId);
        $product->shopId = $this->gateway->getShopId();

        $this->verificator->verify($product, $this->verificationGroups());
        $this->gateway->recordUpdate(
            $product->sourceId,
            $this->productHasher->hash($product),
            $this->revisionProvider->next(),
            $product
        );
    }

    /**
     * Record product availability update
     *
     * Establish a hook in your shop and call this method for every update of a
     * product availability, which is exported to Shopware-Connect.
     *
     * @param string $productId
     * @return void
     */
    public function recordAvailabilityUpdate($productId)
    {
        $product = $this->getProduct($productId);
        $product->shopId = $this->gateway->getShopId();

        $this->verificator->verify($product, $this->verificationGroups());

        $this->gateway->recordAvailabilityUpdate(
            $product->sourceId,
            $this->productHasher->hash($product),
            $this->revisionProvider->next(),
            $product
        );
    }

    /**
     * Record product delete
     *
     * Establish a hook in your shop and call this method for every delete of a
     * product, which is exported to Shopware-Connect.
     *
     * @param string $productId
     * @return void
     */
    public function recordDelete($productId)
    {
        $this->gateway->recordDelete(
            $productId,
            $this->revisionProvider->next()
        );
    }

    public function recordStreamAssignment($productId, array $supplierStreams, $groupId = null)
    {
        $this->gateway->recordStreamAssignment(
            $productId,
            $this->revisionProvider->next(),
            $supplierStreams,
            $groupId
        );
    }

    public function recordStreamDelete($streamId)
    {
        $this->gateway->recordStreamDelete(
            $streamId,
            $this->revisionProvider->next()
        );
    }

    public function makeMainVariant(MakeMainVariant $mainVariant)
    {
        $this->verificator->verify($mainVariant);

        $this->gateway->makeMainVariant(
            $mainVariant->sourceId,
            $this->revisionProvider->next(),
            $mainVariant->groupId
        );
    }

    /**
     * @param PaymentStatusStruct $paymentStatus
     */
    public function updatePaymentStatus(PaymentStatusStruct $paymentStatus)
    {
        $this->verificator->verify($paymentStatus);

        $this->gateway->updatePaymentStatus(
            $this->revisionProvider->next(),
            $paymentStatus
        );
    }

    /**
     * @return array
     */
    protected function verificationGroups()
    {
        return array('default', 'priceExport');
    }

    /**
     * Get single product from gateway
     *
     * @param mixed $productId
     * @return \Shopware\Connect\Struct\Product
     */
    protected function getProduct($productId)
    {
        $products = $this->fromShop->getProducts(array($productId));
        return reset($products);
    }
}
