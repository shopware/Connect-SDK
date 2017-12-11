# v2.0.7

Added some new fields to the `Shopware\Connect\Struct\Product` to be able to sync 
Cross-Selling products

* `(array) $related`
* `(array) $similar`

# v2.0.6

Added some new fields to the `Shopware\Connect\Struct\Product` class which makes
it available to be used in Shopware Connect's SocialNetwork and Updater instead of the deprecated class
`Bepado\Common\Struct\Product\ShopProduct`. The new fields are:

* `(int)  $productId`
* `(int)  $marketplaceId`
* `(Date) $createdAt`
* `(array)$suppliersStreams`

When checking if a product is a variant product you're encouraged to check, if `$groupId` is set
instead of using the old `$hasVariants` field.

# v2.0.5

Added function in ProductToShop to update the OrderStatus of an order with Connect-Products. 
The Connect-Plattform will aggregate the informations of all Connect-Orders within one Order in 
the Merchants-Backend and update the status of these order. All custom implementations of 
ProductToShop will need to implement these function when upgrading to SDK v2.0.2.
Please take a look at the ProductToShop interface for a detailed documentation of this function.

    $productToShop->updateOrderStatus(($localOrderId, $orderStatus, $trackingNumber);

# v2.0.1

Add support for Product Stream assignments directly in SDK. This is an optional
feature where products can be assigned to streams directly in the SDK, and
Shopware Connect platform uses this information automatically to create Streams
for the Shop.

    $sdk->recordStreamAssignment($productId, array('stream-x', 'stream-y'));

# v2.0 - Upgrade from bepado SDK to Shopware Connect SDK

The new stable version of the bepado SDK introduced several backwards
incompatible changes that are necessary to improve the usabililty and
functionality of bepado. The product was renamed from bepado to Shopware Connect
as well, with the respective changes to namespaces of classes.

## Class and Table Renames

All namespaces `Bepado\SDK` where changed to `Shopware\Connect`.  Renamed all
SQL tables form `bepado_` to `sw_connect_`.

## Code Changes

- `\Bepado\SDK\ProductToShop` interface has a two new methods for specialized
  updates that your plugin must implement:

    - `changeAvailability($shopId, $sourceId, $availability);`
    - `update($shopId, $sourceId, ProductUpdate $product);`

- `\Bepado\SDK\ProductFormShop` interface has one new method to calculate
  the remote shops shipping costs for a basket of products using the shop
  systems internal shipping cost logic. New method to perform fromShop changes.
  It can be used to store revision for each product.

    - `calculateShippingCosts(Struct\Order $order);`
    - `onPerformSync($since, array $changes);`

- `\Bepado\SDK\ProductPayments` interface was removed and the method
  `updatePaymentStatus()` is now part of the `Bepado\SDK\ProductFromShop`
  interface.

- Error Handler was improved to throw a custom exception
  `Bepado\SDK\Exception\RemoteException` to notify developers that the
  exception is triggered on the remote host.

## Changes to Purchase Prices

In version 1.0 of the SDK the purchase prices where verified during a
transaction by comparing their values on both sides. If they didn't match, the
transaction was rejected. This required Shopware Connect and the SDK to know about the
discount between a merchant and a provider, which is a single global number.

In version 2.0 products can have different discounts, which requires changes to
the way we guarantee that a purchase price has not been tampered with by a
malicious merchant.

First we have added a new property "offerValidUntil" that contains a unix
timestamp with the date the current purchase price is valid for. Then Shopware Connect
creates a message authentication key from the current purchase price and offer date,
signed with the api key of the provider shop.

Remember, the api key is a secret that is only known to the shop and to Shopware Connect.
This means the merchant shop cannot fake a purchase price hash for the
provider.

The following changes are necessary to adapt your plugin to the new v2.0 approach:

- In your implementation of `Shopware\Connect\ProductToShop::insertOrUpdate()`, retrieve the
  new properties `Product#purchasePriceHash` (string, 255 chars) and `Product#offerValidUntil` (int(10)).
  Safe both in your local product catalogue.

- When you want to recreate a `Shopware\Connect\Struct\Product` for either
  `SDK::checkProducts()`, `SDK::reserveProducts()` or `SDK::checkout()` then
  you must use the values for `Product#purchasePriceHash` and `Product#offerValidUntil` that
  you stored before.

- Note: Setting values for `Product#purchasePriceHash` and
  `Product#offerValidUntil` in the `ProductFromShop::getProducts()` is not
  required. Only on the merchant site of a plugin this two fields are relevant.

If you fail to pass on the purchase price, purchase price hash and offer valid
date during a transaction, then the product will be marked as unavailable and
the transaction is aborted.

## Shipping Cost Calculation

The shipping costs are now calculated by the remote shop and returned when
calling `checkProducts()` on the SDK. This requires some API changes:

- The method `calculateShippingCosts()` has been removed from the `SDK` class.
  Making this still available would invite the user to execute multiple (slow)
  remote calls.

- The method `checkProducts()` now requires an `Order` instead of an array of
  `Product` objects, since an address is required for shipping cost calculation.

- The method `checkProducts()` now always returns a `CheckResult`, which has a
  method `hasErrors()` to check for errors. The `errors` property contains all
  errors. In case of success the `aggregatedShippingCosts` property contains
  the aggregated shipping costs for all remote products.

The `Order` usually is required anyways to reserve products and this is usually
done directly when viewing the checkout already. Thus the API change of the
`checkProducts()` method should actually simplify code. The return values must
be handled differently, though.

You SHOULD NOT call `checkProducts()` for each view of the mini basket and
might want to cache its result. The method will execute (multiple) remote
calls, which can be fairly slow. It will calculate and return the shipping
costs. Thus it should be (at least) executed again once the remote products
contained in the basket changed.
