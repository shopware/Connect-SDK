<?php

namespace Shopware\Connect\Struct;

use Shopware\Connect\Struct;
use Shopware\Connect\Exception\VerificationFailedException;

class VerificatorTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionWhenNoGroupRulesExist()
    {
        $this->setExpectedException(
            'Shopware\Connect\Exception\VerificationFailedException',
            "Cannot verify class 'Shopware\Connect\Struct\Dummy' because no validation rules exist for group 'unknown'."
        );

        $verificator = new DummyVerificator();
        $verificator->verify(
            \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher'),
            new Dummy(),
            array('unknown')
        );
    }

    public function testFailThrowsException()
    {
        $this->setExpectedException(
            'Shopware\Connect\Exception\VerificationFailedException',
            "this is fail 'foo'."
        );

        $verificator = new DummyVerificator();
        $verificator->verify(
            \Phake::mock('Shopware\Connect\Struct\VerificatorDispatcher'),
            new Dummy(),
            array('fail')
        );
    }
}

class Dummy extends Struct
{
}

class DummyVerificator extends Verificator
{
    protected function verifyDefault(VerificatorDispatcher $dispatcher, Struct $struct)
    {
    }

    protected function verifyFail(VerificatorDispatcher $dispatcher, Struct $struct)
    {
        $this->fail("this is fail '%s'.", "foo");
    }
}
