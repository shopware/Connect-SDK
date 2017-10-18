<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect;

/**
 * Interface for product importers
 *
 * Implement this interface with shop specific details to update products in
 * your shop database, which originate from bepado.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 * @api
 */
interface ProductToShop
{
    /**
     * Import or update given product
     *
     * Store product in your shop database as an external product. The
     * associated sourceId
     *
     * @param Struct\Product $product
     */
    public function insertOrUpdate(Struct\Product $product);

    /**
     * Delete product with given shopId and sourceId.
     *
     * Only the combination of both identifies a product uniquely. Do NOT
     * delete products just by their sourceId.
     *
     * You might receive delete requests for products, which are not available
     * in your shop. Just ignore them.
     *
     * @param string $shopId
     * @param string $sourceId
     * @return void
     */
    public function delete($shopId, $sourceId);

    /**
     * Update basic product details.
     *
     * @param string $shopId
     * @param string $sourceId
     * @param ProductUpdate $product
     * @return void
     */
    public function update($shopId, $sourceId, Struct\ProductUpdate $product);

    /**
     * Change the availability of a product with a given shopId and sourceId.
     *
     * Only the combination of both identifies a product uniquely. Do NOT
     * update products just by their sourceId.
     *
     * You might receive change requests for products, which are not available
     * in your shop. Just ignore them.
     *
     * @param string $shopId
     * @param string $sourceId
     * @param int $availability
     * @return void
     */
    public function changeAvailability($shopId, $sourceId, $availability);

    /**
     * Start transaction
     *
     * Starts a transaction, which includes all insertOrUpdate and delete
     * operations, as well as the revision updates.
     *
     * @return void
     */
    public function startTransaction();

    /**
     * Commit transaction
     *
     * Commits the transactions, once all operations are queued.
     *
     * @return void
     */
    public function commit();

    /**
     * Make main variant of a product with given sourceId and groupId.
     *
     * @param int $shopId
     * @param string $sourceId
     * @param string $groupId
     * @return void
     */
    public function makeMainVariant($shopId, $sourceId, $groupId);

    /**
     * Updates the status of an Order
     *
     * $localOrderId is the ordernumber of the order in your shop
     * and order status is the combined Status of all Connect Orders in that local Order
     *
     * e.g. The order contains Product A from Supplier 1 and Product B from Supplier 2
     * if none of both delivered their Product order Status will be OrderStatus::STATE_OPEN
     * if just one of these orders is delivered orderStatus will be OrderStatus::STATE_IN_PROCESS
     * what probably is mapped by Shopware Status partially delivered
     * if both suppliers delivered their products status will be OrderStatus::STATE_DELIVERED
     *
     * if your supplier uses trackingNumbers $trackingNumber contains all TrackingCodes as a comma-seperated string
     * as it's suggested by http://community.shopware.com/_detail_1278.html?_ga=2.247960705.727216894.1508309529-1255644534.1501677320#Anpassung_der_eMail-Vorlage_bei_Vewendung_von_mehreren_Sendungsnummern
     *
     * @param string $localOrderId
     * @param string $orderStatus
     * @param string $trackingNumber
     * @return void
     */
    public function updateOrderStatus($localOrderId, $orderStatus, $trackingNumber);
}
