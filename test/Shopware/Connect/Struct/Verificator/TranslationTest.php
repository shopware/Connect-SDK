<?php

namespace Shopware\Connect\Struct\Verificator;

use Shopware\Connect\Struct;
use Shopware\Connect\ShippingRuleParser;

class TranslationTest extends \PHPUnit_Framework_TestCase
{
    private $verificator;

    private function createValidTranslation()
    {
        return new Struct\Translation(array(
            'title' => 'Rote Strickmütze',
            'shortDescription' => 'Eine rote Strickmütze',
            'longDescription' => 'Eine sehr schöne röte Strickmütze',
            'variantLabels' => array(
                'color' => 'Farbe',
            ),
            'variantValues' => array(
                'red' => 'rot',
            ),
        ));
    }

    public function setUp()
    {
        $this->dispatcher = \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher');
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

        $this->setExpectedException('\Shopware\Connect\Exception\VerificationFailedException');
        $this->verify($translation);
    }

    public function testInvalidVariantLabelKey()
    {
        $translation = $this->createValidTranslation();
        $translation->variantLabels[23] = 'dreiundzwanzig';

        $this->setExpectedException('\Shopware\Connect\Exception\VerificationFailedException');
        $this->verify($translation);
    }

    public function testInvalidVariantLabelValue()
    {
        $translation = $this->createValidTranslation();
        $translation->variantLabels['twentythree'] = 23;

        $this->setExpectedException('\Shopware\Connect\Exception\VerificationFailedException');
        $this->verify($translation);
    }

    public function testInvalidVariantValues()
    {
        $translation = $this->createValidTranslation();
        $translation->variantValues = 23;

        $this->setExpectedException('\Shopware\Connect\Exception\VerificationFailedException');
        $this->verify($translation);
    }

    public function testInvalidVariantValueKey()
    {
        $translation = $this->createValidTranslation();
        $translation->variantValues[23] = 'dreiundzwanzig';

        $this->setExpectedException('\Shopware\Connect\Exception\VerificationFailedException');
        $this->verify($translation);
    }

    public function testInvalidVariantValueValue()
    {
        $translation = $this->createValidTranslation();
        $translation->variantValues['twentythree'] = 23;

        $this->setExpectedException('\Shopware\Connect\Exception\VerificationFailedException');
        $this->verify($translation);
    }
}
