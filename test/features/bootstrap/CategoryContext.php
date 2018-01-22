<?php

namespace Shopware\Connect;


use PHPUnit_Framework_Assert as Assertion;

require_once __DIR__ . '/SDKContext.php';

/**
 * Features context.
 */
class CategoryContext extends SDKContext
{
    private $shopRevision;

    /**
     * @When /^the updater requests the last categories revision$/
     */
    public function theUpdaterRequestsTheLastCategoriesRevision()
    {
        $this->shopRevision = $this->makeRpcCall(
            new Struct\RpcCall(
                [
                    'service' => 'categories',
                    'command' => 'lastRevision',
                    'arguments' => [],
                ]
            )
        );
    }

    /**
     * @Then /^the categories revision is "([^"]*)"$/
     */
    public function theCategoriesRevisionIs($revision)
    {
        Assertion::assertEquals($revision, $this->shopRevision);
    }

    /**
     * @Given /^categories are pushed to the shop with revision "([^"]*)"$/
     */
    public function categoriesArePushedToTheShopWithRevision($revision)
    {
        $this->shopRevision = $this->makeRpcCall(
            new Struct\RpcCall(
                [
                    'service' => 'categories',
                    'command' => 'replicate',
                    'arguments' => [
                        [
                            [
                                'revision' => $revision,
                                'categories' => ['/media/books' => 'Books'],
                            ]
                        ]
                    ]
                ]
            )
        );
    }
}
