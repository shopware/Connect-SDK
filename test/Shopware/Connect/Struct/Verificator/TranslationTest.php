<?php

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\Exception\VerificationFailedException;
use Shopware\Connect\Struct;

class TranslationTest extends \PHPUnit_Framework_TestCase
{
    private $verificator;

    private function createValidTranslation()
    {
        return new Struct\Translation([
            'title' => 'Rote Strickmütze',
            'shortDescription' => 'Eine rote Strickmütze',
            'longDescription' => 'Eine sehr schöne röte Strickmütze',
            'variantLabels' => [
                'color' => 'Farbe',
            ],
            'variantValues' => [
                'red' => 'rot',
            ],
        ]);
    }

    public function setUp()
    {
        $this->dispatcher = $this->createMock(Struct\VerificatorDispatcher::class);
        $this->verificator = new Translation();
    }

    private function verify($translation)
    {
        $this->verificator->verify($this->dispatcher, $translation);
    }

    public function testValidTranslation()
    {
        $this->verify($this->createValidTranslation());
    }

    public function testInvalidVariantLabels()
    {
        $translation = $this->createValidTranslation();
        $translation->variantLabels = 23;

        $this->expectException(VerificationFailedException::class);

        $this->verify($translation);
    }

    public function testInvalidVariantLabelKey()
    {
        $translation = $this->createValidTranslation();
        $translation->variantLabels[23] = 'dreiundzwanzig';

        $this->expectException(VerificationFailedException::class);

        $this->verify($translation);
    }

    public function testInvalidVariantLabelValue()
    {
        $translation = $this->createValidTranslation();
        $translation->variantLabels['twentythree'] = 23;

        $this->expectException(VerificationFailedException::class);

        $this->verify($translation);
    }

    public function testInvalidVariantValues()
    {
        $translation = $this->createValidTranslation();
        $translation->variantValues = 23;

        $this->expectException(VerificationFailedException::class);

        $this->verify($translation);
    }

    public function testInvalidVariantValueKey()
    {
        $translation = $this->createValidTranslation();
        $translation->variantValues[23] = 'dreiundzwanzig';

        $this->expectException(VerificationFailedException::class);

        $this->verify($translation);
    }

    public function testInvalidVariantValueValue()
    {
        $translation = $this->createValidTranslation();
        $translation->variantValues['twentythree'] = 23;

        $this->expectException(VerificationFailedException::class);

        $this->verify($translation);
    }
}
