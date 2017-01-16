<?php

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\SDK;
use Shopware\Connect\Struct;
use Shopware\Connect\ShippingRuleParser;

class PropertyTest extends ProductTest
{

    public function testPropertiesWithEmptyGroupName()
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

    public function testPropertiesWithEmptyOption()
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

    public function testPropertiesWithEmptyValue()
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

    public function testPropertiesWithWrongSortModeNumber()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 3,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property sortMode MUST be 0, 1, or 2"
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

    public function testPropertiesWithWrongGroupPosition()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'groupPosition' => '3',
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property groupPosition MUST be int."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

    public function testPropertiesWithWrongValuePosition()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property(array(
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
            'valuePosition' => '3',
        ));

        $product->properties = array($property);

        $this->setExpectedException(
            '\Shopware\Connect\Exception\VerificationFailedException',
            "Property valuePosition MUST be int."
        );

        $this->verificator->verify($this->dispatcher, $product, array('default'));
    }

}