<?php

namespace Shopware\Connect\ErrorHandler;

use Shopware\Connect\Exception\RemoteException;
use Shopware\Connect\Struct;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertRemoteErrorToException()
    {
        $this->expectException(RemoteException::class);
        $this->expectExceptionMessage('Error');
        $exception = new Exception();
        $exception->handleError(new Struct\Error([
            'message' => 'Error',
        ]));
    }
}
