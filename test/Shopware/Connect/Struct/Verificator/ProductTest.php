<?php

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\Exception\VerificationFailedException;
use Shopware\Connect\SDK;
use Shopware\Connect\Struct;
use Shopware\Connect\ShippingRuleParser;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    protected $verificator;

    protected function createValidProduct()
    {
        return new Struct\Product([
            'shopId' => 10,
            'sourceId' => 10,
            'title' => 'Foo',
            'price' => 10.10,
            'purchasePrice' => 20.20,
            'availability' => 0,
            'vendor' => 'Foo',
            'shortDescription' => 'Foo Bar',
            'longDescription' => 'Bar Foo',
            'variant' => ['color' => 'blue'],
        ]);
    }

    public function setUp()
    {
        $this->dispatcher = $this->createMock(Struct\VerificatorDispatcher::class);
        $this->verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_BOTH
        );
    }

    private function verify($product)
    {
        $this->verificator->verify($this->dispatcher, $product);
    }

    public function testValidProduct()
    {
        $product = $this->createValidProduct();

        $this->verify($product);

        $product->configuratorSetType = null;
        $product->variant = [];

        $this->verify($product);
    }

    public function test_configurator_set_is_invalid_without_variants()
    {
        $product = $this->createValidProduct();
        $product->variant = [];
        $product->configuratorSetType = 1;

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('ConfiguratorSetType has to be in range 0..2');

        $this->verify($product);
    }

    public function test_configurator_set_is_invalid_with_variants()
    {
        $product = $this->createValidProduct();
        $product->configuratorSetType = 4;

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('ConfiguratorSetType has to be in range 0..2');

        $this->verify($product);
    }

    public function testVendorEmptyIsError()
    {
        $product = $this->createValidProduct();
        $product->vendor = null;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property vendor MUST be non-empty.');

        $this->verify($product);
    }

    public function testTitleEmptyIsError()
    {
        $product = $this->createValidProduct();
        $product->title = null;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property title MUST be non-empty.');

        $this->verify($product);
    }

    public function testInvalidShippingRule()
    {
        $product = $this->createValidProduct();
        $product->shipping = 'Invalid:::';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected Delivery name (free text) at position 0 – expected one of: Element separator ":", Country Code (ISO 3166-1) (eg. DE)');

        $this->verify($product);
    }

    public function testValidUnitAttributes()
    {
        $product = $this->createValidProduct();
        $product->attributes[Struct\Product::ATTRIBUTE_UNIT] = 'kg';
        $product->attributes[Struct\Product::ATTRIBUTE_QUANTITY] = 10;
        $product->attributes[Struct\Product::ATTRIBUTE_REFERENCE_QUANTITY] = 100;

        $this->verify($product);
    }

    /**
     * @dataProvider dataValidDimensions
     */
    public function testValidDimensions($dimension)
    {
        $product = $this->createValidProduct();
        $product->attributes[Struct\Product::ATTRIBUTE_DIMENSION] = $dimension;

        $this->verify($product);
    }

    /**
     * @dataProvider dataInvalidDimensions
     */
    public function testInvalidDimensions($dimension)
    {
        $product = $this->createValidProduct();
        $product->attributes[Struct\Product::ATTRIBUTE_DIMENSION] = $dimension;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product Dimensions Attribute has to be in format');

        $this->verify($product);
    }

    public static function dataValidDimensions()
    {
        return [
            ['10x20x30'],
            ['10.5x20.7x30.2'],
            ['1.5x20x30'],
            ['1x20.5x30'],
            ['1x20x30.7'],
        ];
    }

    public static function dataInvalidDimensions()
    {
        return [
            ['axbxc'],
            ['10x10'],
            ['10,4x10,4x10,4'],
        ];
    }

    /**
     * @param mixed $images
     * @dataProvider dataValidImages
     */
    public function testValidImages($images)
    {
        $product = $this->createValidProduct();

        $product->images = $images;

        $this->verify($product);
    }

    public static function dataValidImages()
    {
        return [
            [
                []
            ],
            [
                ['foo', 'bar']
            ],
            [
                [0 => 'foo', 1 => 'bar']
            ],
        ];
    }

    /**
     * @param mixed $images
     * @dataProvider dataInvalidImagesBasetype
     */
    public function testInvalidImagesBasetype($images)
    {
        $product = $this->createValidProduct();

        $product->images = $images;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product#images must be an array.');

        $this->verify($product);
    }

    public static function dataInvalidImagesBasetype()
    {
        return [
            [
                'foo'
            ],
            [
                new \ArrayObject()
            ],
            [
                null
            ],
        ];
    }

    /**
     * @param mixed $images
     * @dataProvider dataInvalidImagesIndexing
     */
    public function testInvalidImagesIndexing($images)
    {
        $product = $this->createValidProduct();

        $product->images = $images;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product#images must be numerically indexed starting with 0.');

        $this->verify($product);
    }

    /**
     * @param mixed $images
     * @dataProvider dataInvalidImagesIndexing
     */
    public function testInvalidVariantImagesIndexing($images)
    {
        $product = $this->createValidProduct();

        $product->variantImages = $images;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product#variantImages must be numerically indexed starting with 0.');

        $this->verify($product);
    }

    /**
     * @param mixed $images
     * @dataProvider dataInvalidImagesBasetype
     */
    public function testInvalidVariantImagesBasetype($images)
    {
        $product = $this->createValidProduct();

        $product->variantImages = $images;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product#variantImages must be an array.');

        $this->verify($product);
    }

    /**
     * @param mixed $images
     * @dataProvider dataValidImages
     */
    public function testValidVariantImages($images)
    {
        $product = $this->createValidProduct();

        $product->variantImages = $images;

        $this->verify($product);
    }

    public function testOnlyScalarAttributes()
    {
        $product = $this->createValidProduct();
        $product->attributes['ref_quantity'] = new \stdClass();

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Attribute ref_quantity MUST be scalar value.');

        $this->verify($product);
    }

    public static function dataInvalidImagesIndexing()
    {
        return [
            [
                ['foo' => 'foo', 'bar' => 'bar']
            ],
            [
                [1 => 'foo', 2 => 'bar']
            ],
        ];
    }

    public function testInvalidTranslationsNotArray()
    {
        $product = $this->createValidProduct();
        $product->translations = 42;

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Product#translations must be an array.');

        $this->verify($product);
    }

    public function testInvalidTranslationKey()
    {
        $product = $this->createValidProduct();
        $product->translations[23] = new Struct\Translation();

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('The keys of Product#translations must be strings.');

        $this->verify($product);
    }

    public function testInvalidTranslationValue()
    {
        $product = $this->createValidProduct();
        $product->translations['en'] = 23;

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Product#translations must contain only instances of \Shopware\Connect\Struct\Translation.');

        $this->verify($product);
    }

    public function testInvalidISOCodeKey()
    {
        $product = $this->createValidProduct();
        $product->translations['pb'] = new Struct\Translation();

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('The keys of Product#translations must only be valid ISO 639-1 codes (e.g. \'de\', \'es\', ...).');

        $this->verify($product);
    }

    public function testInvalidUpperCaseLanguageCodeKey()
    {
        $product = $this->createValidProduct();
        $product->translations['EN'] = new Struct\Translation();

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('The keys of Product#translations must only be valid ISO 639-1 codes (e.g. \'de\', \'es\', ...).');

        $this->verify($product);
    }

    public function testValidCategories()
    {
        $product = $this->createValidProduct();
        $product->categories = [
            '/Kleidung' => 'Kleidung',
            '/Kleidung/Hosen' => 'Hosen',
            '/Kleidung/Hosen/Hosentraeger' => 'Hosenträger',
            '/Nahrung & Getraenke' => 'Nahrung & Getränke',
            '/Nahrung & Getraenke/Alkoholische Getraenke' => 'Alkoholische Getränke',
            '/Nahrung & Getraenke/Alkoholische Getraenke/Bier' => 'Bier',
        ];

        $this->verify($product);
    }

    public function testInvalidCategoriesMissingParent()
    {
        $product = $this->createValidProduct();
        $product->categories = [
            '/Kleidung/Hosen' => 'Hosen',
            '/Kleidung/Hosen/Hosentraeger' => 'Hosenträger',
        ];

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Product#categories must contain all parent categories. Parent category of /Kleidung missing.');

        $this->verify($product);
    }

    public function testFixedPriceWithPriceTypePurchase()
    {
        $product = $this->createValidProduct();
        $product->fixedPrice = true;

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_PURCHASE
        );

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Fixed price is not allowed when export purchasePrice only');

        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testPriceRanges()
    {
        $product = $this->createValidProduct();
        $priceRange = new Struct\PriceRange([
            'from' => 1,
            'to' => 10,
            'price' => 33,
        ]);

        $product->fixedPrice = false;
        $product->priceRanges = [$priceRange];

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_RETAIL
        );
        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testPriceRangesWithWrongFrom()
    {
        $product = $this->createValidProduct();
        $priceRange = new Struct\PriceRange([
            'from' => '1',
            'to' => 10,
            'price' => 13.30,
        ]);

        $product->fixedPrice = false;
        $product->priceRanges = [$priceRange];

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_RETAIL
        );

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage("The price range 'from' must be int and is not allowed to be 0 or smaller.");

        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testPriceRangesWithWrongToString()
    {
        $product = $this->createValidProduct();
        $priceRange = new Struct\PriceRange([
            'from' => 1,
            'to' => 'string',
            'price' => 13.30,
        ]);

        $product->fixedPrice = false;
        $product->priceRanges = [$priceRange];

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_RETAIL
        );

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage("The price range 'to' must be int bigger from 0 or string with value 'any'.");

        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testPriceRangesWithWrongToInt()
    {
        $product = $this->createValidProduct();
        $priceRange = new Struct\PriceRange([
            'from' => 1,
            'to' => 0,
            'price' => 13.30,
        ]);

        $product->fixedPrice = false;
        $product->priceRanges = [$priceRange];

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_RETAIL
        );

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage("The price range 'to' is not allowed to be 0 or smaller.");

        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testPriceRangesWithWrongToValue()
    {
        $product = $this->createValidProduct();
        $priceRange = new Struct\PriceRange([
            'from' => 1,
            'to' => 3.2,
            'price' => 13.30,
        ]);

        $product->fixedPrice = false;
        $product->priceRanges = [$priceRange];

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_RETAIL
        );

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage("The price range 'to' must be int or string.");

        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testPriceRangesWithWrongPrice()
    {
        $product = $this->createValidProduct();
        $priceRange = new Struct\PriceRange([
            'from' => 1,
            'to' => 3,
            'price' => -12,
        ]);

        $product->fixedPrice = false;
        $product->priceRanges = [$priceRange];

        $verificator = new Product(
            new ShippingRuleParser\Google(),
            SDK::PRICE_TYPE_RETAIL
        );

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('The price is not allowed to be 0 or smaller.');

        $verificator->verify($this->dispatcher, $product, ['default', 'priceExport']);
    }

    public function testProductDescriptionLimit()
    {
        $product = $this->createValidProduct();
        $product->shortDescription = str_repeat('Short Sasse Münsterländer Lagerkorn 32% 0,2L', 200000);
        $product->longDescription = str_repeat('Long Sasse Münsterländer Lagerkorn 32% 0,2L', 200000);
        $product->additionalDescription = str_repeat('Additional Sasse Münsterländer Lagerkorn 32% 0,2L', 10000);

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Product short, long and additional description must be under 5 000 000 characters.');

        $this->verify($product);
    }

    public function testZeroMinPurchaseQuantity()
    {
        $product = $this->createValidProduct();
        $product->minPurchaseQuantity = 0;

        $this->expectException(VerificationFailedException::class);
        $this->expectExceptionMessage('Product#minPurchaseQuantity must be positive, greater than 0.');

        $this->verify($product);
    }
}
