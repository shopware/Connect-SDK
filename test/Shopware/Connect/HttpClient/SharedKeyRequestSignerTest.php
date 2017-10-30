<?php

namespace Shopware\Connect\HttpClient;

use Shopware\Connect\Struct\ShopConfiguration;
use Shopware\Connect\Service\Clock;
use Shopware\Connect\Gateway;

class SharedKeyRequestSignerTest extends \PHPUnit_Framework_TestCase
{
    private $gatewayMock;

    public function setUp()
    {
        $this->gatewayMock = $this->createMock(Gateway\ShopConfiguration::class);
    }

    public function testSignRequest()
    {
        $this->gatewayMock->expects($this->any())
            ->method('getShopConfiguration')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new ShopConfiguration(array('key' => 1234))));

        $this->gatewayMock->expects($this->once())
            ->method('getShopId')
            ->will($this->returnValue(1337));

        $time = 1234567890;

        $clock = $this->createMock(Clock::class);
        $clock->expects($this->once())->method('time')->will($this->returnValue($time));

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, null);

        $headers = $signer->signRequest(42, '<xml body>');

        $this->assertEquals(array(
                'Authorization: SharedKey party="1337",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'X-Shopware-Connect-Authorization: SharedKey party="1337",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'Date: Fri, 13 Feb 2009 23:31:30 GMT',
            ), $headers);
    }

    public function testVerifyBepadoRequest()
    {
        $this->gatewayMock->expects($this->never())->method('getShopConfiguration');

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");
        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_AUTHORIZATION' => 'SharedKey party="connect",nonce="800b055230b317aa24bc27c02ee02997cbfdf7969deda30804d55a6d59a3fcb528cf971e25033b6b37b4e99bcdd4b95de56352f486d1ebb63b5a2d4d42b41eef"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertTrue($token->authenticated, "Authorization Header is valid");
        $this->assertEquals("connect", $token->userIdentifier);
    }

    public function testVerifyBepadoRequestFallbackCustomAuthHeader()
    {
        $this->gatewayMock->expects($this->never())->method('getShopConfiguration');

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");
        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'SharedKey party="connect",nonce="800b055230b317aa24bc27c02ee02997cbfdf7969deda30804d55a6d59a3fcb528cf971e25033b6b37b4e99bcdd4b95de56352f486d1ebb63b5a2d4d42b41eef"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertTrue($token->authenticated, "Authorization Header is valid");
        $this->assertEquals("connect", $token->userIdentifier);
    }

    public function testVerifyShopRequest()
    {
        $this->gatewayMock->expects($this->once())
            ->method('getShopConfiguration')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new ShopConfiguration(array('key' => 1234))));

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");
        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_AUTHORIZATION' => 'SharedKey party="42",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertTrue($token->authenticated, "Authorization Header is valid");
        $this->assertEquals(42, $token->userIdentifier);
    }

    public function testVerifyShopRequestFallbackCustomAuthHeader()
    {
        $this->gatewayMock->expects($this->once())
            ->method('getShopConfiguration')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new ShopConfiguration(array('key' => 1234))));

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");
        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'SharedKey party="42",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertTrue($token->authenticated, "Authorization Header is valid");
        $this->assertEquals(42, $token->userIdentifier);
    }

    public function testVerifyErrorMissingAuthHeaders()
    {
        $this->configureDefaultGatewayMock();

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");

        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertFalse($token->authenticated, 'Missing auth headers not detected.');
        $this->assertEquals(
            'No authorization header found. Only: HTTP_DATE',
            $token->errorMessage
        );
    }

    public function testVerifyErrorMissingDateHeader()
    {
        $this->configureDefaultGatewayMock();

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");

        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'SharedKey party="42",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
            )
        );

        $this->assertFalse($token->authenticated, 'Missing date header not detected.');
        $this->assertEquals(
            'No date header found.',
            $token->errorMessage
        );
    }

    public function testVerifyErrorIncorrectAuthType()
    {
        $this->configureDefaultGatewayMock();

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");

        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'FooBar party="42",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertFalse($token->authenticated, 'Incorrect auth type not detected.');
        $this->assertEquals(
            'Authorization type is not "SharedKey".',
            $token->errorMessage
        );
    }

    public function testVerifyErrorUnrecognizedParty()
    {
        $this->configureDefaultGatewayMock();

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");

        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'SharedKey party="foobar",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertFalse($token->authenticated, 'Incorrect auth type not detected.');
        $this->assertEquals(
            'Unrecognized party in SharedKey authorization.',
            $token->errorMessage
        );
    }

    public function testVerifyErrorNounceIncorrect()
    {
        $this->configureDefaultGatewayMock();

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");

        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'SharedKey party="connect",nonce="abc-die-katze-lief-im-schnee"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertFalse($token->authenticated, 'Incorrect nounce not detected.');
        $this->assertEquals(
            'Nounce does not match.',
            $token->errorMessage
        );
    }

    public function testArktisRegression()
    {
        $this->configureDefaultGatewayMock();

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");

        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'PATH' => '/usr/local/bin:/usr/bin:/bin',
                'PHPRC' => '/var/www/web5/fcgi',
                'PWD' => '/var/www/web5/fcgi',
                'HTTP_CONNECTION' => 'close',
                'SCRIPT_NAME' => '/shopware.php',
                'REQUEST_URI' => '/backend/bepado_gateway',
                'QUERY_STRING' => '',
                'REQUEST_METHOD' => 'POST',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'GATEWAY_INTERFACE' => 'CGI/1.1',
                'REDIRECT_URL' => '/backend/bepado_gateway',
                'REMOTE_PORT' => '35213',
                'SCRIPT_FILENAME' => '/var/www/web5/html/shop-update/shopware.php',
                'SERVER_ADMIN' => '[no address given]',
                'DOCUMENT_ROOT' => '/var/www/web5/html/shop-update',
                'REMOTE_ADDR' => '37.44.4.37',
                'SERVER_PORT' => '80',
                'SERVER_ADDR' => '212.53.175.13',
                'SERVER_NAME' => 'testshop.arktis.de',
                'SERVER_SOFTWARE' => 'Apache/2.2.22 (Debian)',
                'SERVER_SIGNATURE' => '<address>Apache/2.2.22 (Debian) Server at testshop.arktis.de Port 80</address>
                    ',
                'CONTENT_LENGTH' => '1644',
                'HTTP_DATE' => 'Tue, 04 Feb 2014 14:49:52 GMT',
                'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION' => 'SharedKey party="connect",nonce="16fc7ae51b4617134a1d7264f379e20e639aef53d871264bd2b2b85ef175aced43fa98e2483fa7940312e0b93bf9b9d284975b216f353108332b09687d4a6e48"',
                'HTTP_USER_AGENT' => 'Guzzle/3.8.1 curl/7.31.0 PHP/5.4.20-pl0-gentoo',
                'HTTP_HOST' => 'testshop.arktis.de',
                'REDIRECT_STATUS' => '200',
                'FCGI_ROLE' => 'RESPONDER',
                'PHP_SELF' => '/shopware.php',
                'REQUEST_TIME_FLOAT' => 1391525392.7312,
                'REQUEST_TIME' => 1391525392,
                'HTTP_SURROGATE_CAPABILITY' => 'shopware="ESI/1.0"',
            )
        );

        $this->assertFalse($token->authenticated, 'Incorrect nounce not detected.');
        $this->assertEquals(
            'Nounce does not match.',
            $token->errorMessage
        );
    }

    public function testVerifyErrorNotSyncedSharedKey()
    {
        $this->gatewayMock->expects($this->once())
            ->method('getShopConfiguration')
            ->with($this->equalTo(42))
            ->will($this->returnValue(new ShopConfiguration()));

        $clock = $this->createMock(Clock::class);

        $signer = new SharedKeyRequestSigner($this->gatewayMock, $clock, "aaa-bbb-ccc-ddd");
        $token = $signer->verifyRequest(
            '<xml body>',
            array(
                'HTTP_AUTHORIZATION' => 'SharedKey party="42",nonce="de93510785d31758983da9a65fd7216c280cd41248a26ff25af037c97a4b31fb0a63fa2906b763b31601448f6cc3563c9c3afa4dcf557fa714129af302780f7a"',
                'HTTP_DATE' => 'Fri, 13 Feb 2009 23:31:30 GMT'
            )
        );

        $this->assertFalse($token->authenticated, 'SharedKey not synced yet.');
        $this->assertEquals(
            'Missing SharedKey.',
            $token->errorMessage
        );
    }

    private function configureDefaultGatewayMock()
    {
        $this->gatewayMock->expects($this->any())
            ->method('getShopConfiguration')
            ->will($this->returnValue(new ShopConfiguration(array('key' => 1234))));
    }
}
