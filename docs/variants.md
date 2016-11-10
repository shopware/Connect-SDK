# Variants

Starting with the 1st release in March 2015, Shopware Connect has support for variants on
the staging system. This will most likely be added to production in the second
half of March.

- The changes to the SDK and Shopware Connect platform have been designed in a
  backwards compatible way. There is no hurry to implement it or update
  your SDK.
- Variants are **an optional feature**, plugins don't have to implement it.
  Plugin authors can also decide to implement it just for export or import
  or for both.

To use variants you must have at least SDK version 1.6.0 (current is 1.6.4 in
March 2015).

The big picture on variants is that for each variant of your variant-product
you export one SDK Product. That means our data model considers every variant
to be its own product in Shopware Connect. The variants are combined using a group
identifier, much like the Google Product Merchant Feed uses the
`item_group_id`.

The data model for a Shopware Connect Product was enriched with two new fields that are
necessary to export variant products:

- A Variant Group ID (`groupId`) that must be an identifier that all variants
  share. This field is optional. You should only set it if your product really
  has variants.

  Important: The `sourceId` field must be unique across *ALL* your exported
  products. If you violate this and have source ids unique to your variant group
  only, then products will override each other.

  Example: T-Shirt Red and Blue have their own unique `sourceId` 1 and 2,
  however to be grouped as a single variant product in Shopware Connect, they share the
  same `groupId` = 10.

- An array for all the variant attributes (`variants`) with key value pairs
  using the name of the variant attribute and its value.

  Adding multiple variant attributes allows to add multi-dimensional variant information.

  Example: T-Shirt red has one variant attribute: `"color" => "red"`,
  T-Shirt Blue has the attribute `"color" => "blue"`.

- The `title` attribute should contain a human readable description of the variant,
  this makes it easier for others to import the product variants in their shops.

## Exporting Variant Products

When exporting variant products you must add these values to the `Shopware\Connect\Struct\Product`
like in the following example code:

    <?php

    $sdkProduct = new \Shopware\Connect\Struct\Product();

    if ($shopProduct->isVariant()) {
        $sdkProduct->groupId = $shopProduct->getMasterProductId();

        foreach ($shopProduct->getVariantAttributes() as $attribute => $value) {
            $sdkProduct->variant[$attribute] = $value;
        }
    }

## Importing Variant Products

It is the responsibility of the plugin author to aggregate variant products
that are sent through Shopware Connect to the plugin shop. When variants are ignored,
your plugin will create one product for each variant.

You can decide to import the products using multiple dimensions or a single
dimension. Either use the property ``$product->variant`` to automatically
create the Variant data for your product or call the method
``$product->getVariantString()`` to get a combined single variant attribute for
this product.
