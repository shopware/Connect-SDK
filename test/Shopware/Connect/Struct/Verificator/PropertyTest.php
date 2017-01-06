<?php

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\SDK;
use Shopware\Connect\Struct;
use Shopware\Connect\ShippingRuleParser;

class PropertyTest extends ProductTest
{

    public function testPropertiesWithWrongGroupName()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property groupName MUST be non-empty."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

    public function testPropertiesWithWrongOption()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 1,
            'option' => '',
            'filterable' => false,
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property option MUST be non-empty."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

    public function testPropertiesWithWrongValue()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => '',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property value MUST be non-empty."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

    public function testPropertiesWithWrongComparable()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => 'false',
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property comparable MUST be boolean."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

    public function testPropertiesWithWrongFilterable()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property filterable MUST be boolean."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }



    public function testPropertiesWithWrongSortMode()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => '1',
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property sortMode MUST be int."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }
}