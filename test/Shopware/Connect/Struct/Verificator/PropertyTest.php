<?php

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\Exception\VerificationFailedException;
use Shopware\Connect\Struct;

class PropertyTest extends ProductTest
{
    public function testPropertiesWithEmptyGroupName()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property groupName MUST be non-empty.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithEmptyOption()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 1,
            'option' => '',
            'filterable' => false,
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property option MUST be non-empty.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithEmptyValue()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => '',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property value MUST be non-empty.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithWrongComparable()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => 'false',
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property comparable MUST be boolean.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithWrongFilterable()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 0,
            'option' => 'Option',
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property filterable MUST be boolean.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithWrongSortMode()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => '1',
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property sortMode MUST be int.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithWrongSortModeNumber()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 2,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property sortMode MUST be 0, 1, or 3');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithWrongGroupPosition()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'groupPosition' => '3',
            'comparable' => false,
            'sortMode' => 1,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property groupPosition MUST be int.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }

    public function testPropertiesWithWrongValuePosition()
    {
        $product = $this->createValidProduct();
        $property = new Struct\Property([
            'groupName' => 'GroupName',
            'comparable' => false,
            'sortMode' => 3,
            'option' => 'Option',
            'filterable' => false,
            'value' => 'Value',
            'valuePosition' => '3',
        ]);

        $product->properties = [$property];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Property valuePosition MUST be int.');

        $this->verificator->verify($this->dispatcher, $product, ['default']);
    }
}
