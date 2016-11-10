<?php

namespace Shopware\Connect\ErrorHandler;

use Shopware\Connect\Struct;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertRemoteErrorToException()
    {
        $this->setExpectedException('Shopware\Connect\Exception\RemoteException', 'Error');

        $exception = new Exception();
        $exception->handleError(new Struct\Error(array(
            'message' => 'Error',
        )));
    }
}
