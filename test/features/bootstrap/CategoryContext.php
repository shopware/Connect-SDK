<?php

namespace Shopware\Connect;

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Shopware\Connect\Struct\Product;
use Shopware\Connect\Struct\Change;
use Shopware\Connect\Rpc;
use Shopware\Connect\Struct;

use \PHPUnit_Framework_Assert as Assertion;

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
                array(
                    'service' => 'categories',
                    'command' => 'lastRevision',
                    'arguments' => array(),
                )
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
                array(
                    'service' => 'categories',
                    'command' => 'replicate',
                    'arguments' => array(
                        array(
                            array(
                                'revision' => $revision,
                                'categories' => array('/media/books' => 'Books'),
                            )
                        )
                    )
                )
            )
        );
    }
}
