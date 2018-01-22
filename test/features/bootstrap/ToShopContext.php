<?php

namespace Shopware\Connect;

use Shopware\Connect\Struct\Product;
use Shopware\Connect\Struct\Change;

use PHPUnit_Framework_Assert as Assertion;

require_once __DIR__ . '/SDKContext.php';

/**
 * Features context.
 */
class ToShopContext extends SDKContext
{
    protected $changes = [];

    protected $productId = 1;

    protected $shopRevision = null;

    /**
     * @Given /^The shop did not synchronize any products$/
     */
    public function theShopDidNotSynchronizeAnyProducts()
    {
        // Nothing?
    }

    /**
     * @Given /^Bepado has been configured to export (\d+) products$/
     */
    public function bepadoHasBeenConfiguredToExportProducts($productCount)
    {
        $end = $this->productId + $productCount;
        for (; $this->productId < $end; ++$this->productId) {
            $this->changes[] = new Change\ToShop\InsertOrUpdate(
                [
                    'sourceId' => $this->productId,
                    'revision' => $this->productId,
                    'product' => $this->getProduct($this->productId),
                ]
            );
        }
    }

    /**
     * @Given /^The shop already synchronized (\d+) exported products$/
     */
    public function theShopAlreadySynchronizedExportedProducts($productCount)
    {
        $this->syncChanges($productCount);
    }

    /**
     * @Given /^(\d+) products have been updated$/
     */
    public function productsHaveBeenUpdated($productCount)
    {
        for ($i = 0; $i < $productCount; ++$i) {
            $this->changes[] = new Change\ToShop\InsertOrUpdate(
                [
                    'sourceId' => $i,
                    'revision' => $i,
                    'product' => $this->getProduct($i),
                ]
            );
        }
    }

    /**
     * @Given /^(\d+) products have been deleted$/
     */
    public function productsHaveBeenDeleted($productCount)
    {
        for ($i = 0; $i < $productCount; ++$i) {
            $this->changes[] = new Change\ToShop\Delete(
                [
                    'sourceId' => $i,
                    'shopId' => 'shop-1',
                    'revision' => $i,
                ]
            );
        }
    }

    protected function syncChanges($count)
    {
        $process = array_slice($this->changes, 0, $count);
        $this->changes = array_slice($this->changes, $count);
        $this->shopRevision = $this->makeRpcCall(
            new Struct\RpcCall(
                [
                    'service' => 'products',
                    'command' => 'replicate',
                    'arguments' => [
                        $process
                    ]
                ]
            )
        );
    }

    /**
     * @When /^Export is triggered$/
     */
    public function exportIsTriggered()
    {
        // Nothing?
    }

    /**
     * @Then /^(\d+) updates are triggered$/
     */
    public function updatesAreTriggered($productCount)
    {
        Assertion::assertEquals(
            $this->shopRevision,
            $this->makeRpcCall(
                new Struct\RpcCall(
                    [
                        'service' => 'products',
                        'command' => 'lastRevision',
                        'arguments' => [],
                    ]
                )
            )
        );

        Assertion::assertEquals(
            $productCount,
            count($this->changes)
        );

        Assertion::assertEquals(
            $productCount,
            count($this->changes)
        );

        $this->syncChanges($productCount);
    }
}
