<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\HttpClient;

use Shopware\Connect\Gateway\ShopConfiguration;
use Shopware\Connect\Service\Clock;
use Shopware\Connect\Struct\AuthenticationToken;

class SharedKeyRequestSigner implements RequestSigner
{
    const HTTP_AUTH_HEADER = 'Authorization';

    const HTTP_AUTH_HEADER_KEY = 'HTTP_AUTHORIZATION';

    /**
     * Custom HTTP header to ship around web servers filtering "Authorization".
     */
    const HTTP_CUSTOM_AUTH_HEADER = 'X-Shopware-Connect-Authorization';

    /**
     * $_SERVER key for custom HTTP header
     */
    const HTTP_CUSTOM_AUTH_HEADER_KEY = 'HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION';

    const HTTP_DATE_HEADER = 'Date';

    const HTTP_DATE_HEADER_KEY = 'HTTP_DATE';

    /**
     * @param ShopConfiguration
     */
    private $gateway;

    /**
     * @param Clock
     */
    private $clock;

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(ShopConfiguration $gateway, Clock $clock, $apiKey)
    {
        $this->gateway = $gateway;
        $this->clock = $clock;
        $this->apiKey = $apiKey;
    }

    /**
     * Return array of headers required to sign particular request.
     *
     * @param int $shopId
     * @param string $body
     * @return array
     */
    public function signRequest($shopId, $body)
    {
        $configuration   = $this->gateway->getShopConfiguration($shopId);
        $verificationKey = $configuration->key;
        $myShopId        = $this->gateway->getShopId();
        $requestDate     = gmdate('D, d M Y H:i:s', $this->clock->time()) . ' GMT';
        $nonce           = $this->generateNonce($requestDate, $body, $verificationKey);

        $authHeaderContent = 'SharedKey party="' . $myShopId . '",nonce="' . $nonce . '"';

        return [
            $this->createHttpHeader(self::HTTP_AUTH_HEADER, $authHeaderContent),
            $this->createHttpHeader(self::HTTP_CUSTOM_AUTH_HEADER, $authHeaderContent),
            $this->createHttpHeader(self::HTTP_DATE_HEADER, $requestDate)
        ];
    }

    /**
     * @param string $headerName
     * @param string $headerContent
     */
    private function createHttpHeader($headerName, $headerContent)
    {
        return sprintf('%s: %s', $headerName, $headerContent);
    }

    /**
     * Verify that a given message is valid.
     *
     * @param string $body
     * @param array $headers
     * @return bool
     */
    public function verifyRequest($body, array $headers)
    {
        $authHeader = $this->getAuthorizationHeader($headers);

        if ($authHeader == '') {
            return new AuthenticationToken(
                [
                    'authenticated' => false,
                    'errorMessage' => 'No authorization header found. Only: ' . $this->getHeaderNames($headers),
                ]
            );
        }

        if (!isset($headers[self::HTTP_DATE_HEADER_KEY])) {
            return new AuthenticationToken(
                [
                    'authenticated' => false,
                    'errorMessage' => 'No date header found.',
                ]
            );
        }

        list($type, $params) = explode(' ', $authHeader, 2);

        if ($type !== 'SharedKey') {
            return new AuthenticationToken(
                [
                    'authenticated' => false,
                    'errorMessage' => 'Authorization type is not "SharedKey".',
                ]
            );
        }

        $party = '';
        if (preg_match('(^(party="([^"]+)\",nonce="([^"]+)")$)', $params, $matches)) {
            $party = $matches[2];
            $actualNonce = $matches[3];

            if ($party === 'connect') {
                $verificationKey = $this->apiKey;
            } elseif (is_numeric($party)) {
                $configuration = $this->gateway->getShopConfiguration($party);
                if (!isset($configuration->key)) {
                    return new AuthenticationToken(
                        [
                            'authenticated' => false,
                            'userIdentifier' => $party,
                            'errorMessage' => 'Missing SharedKey.',
                        ]
                    );
                }
                $verificationKey = $configuration->key;
                $party = (int) $party;
            } else {
                return new AuthenticationToken(
                    [
                        'authenticated' => false,
                        'errorMessage' => 'Unrecognized party in SharedKey authorization.'
                    ]
                );
            }

            $expectedNonce = $this->generateNonce($headers['HTTP_DATE'], $body, $verificationKey);

            if ($this->stringsEqual($actualNonce, $expectedNonce)) {
                return new AuthenticationToken(['authenticated' => true, 'userIdentifier' => $party]);
            }

            return new AuthenticationToken(
                [
                    'authenticated' => false,
                    'userIdentifier' => $party,
                    'errorMessage' => 'Nounce does not match.',
                ]
            );
        }

        return new AuthenticationToken(
            [
                'authenticated' => false,
                'userIdentifier' => $party,
                'errorMessage' => 'Could not match SharedKey elements at invalid nounce.',
            ]
        );
    }

    /**
     * @param array $headers
     * @return string
     */
    private function getHeaderNames($headers)
    {
        return implode(
            ', ',
            array_filter(
                array_keys($headers),
                function ($header) {
                    return strpos($header, 'HTTP_') === 0;
                }
            )
        );
    }

    /**
     * Returns the content of the Shopware Connect authorization header.
     *
     * This method tries:
     *
     * - HTTP_AUTHORIZATION
     * - HTTP_X_SHOPWARE_CONNECT_AUTHORIZATION
     *
     * If none of them worked, it returns an empty string
     *
     * @param array $headers
     */
    private function getAuthorizationHeader(array $headers)
    {
        if (isset($headers[self::HTTP_AUTH_HEADER_KEY])) {
            return $headers[self::HTTP_AUTH_HEADER_KEY];
        }
        if (isset($headers[self::HTTP_CUSTOM_AUTH_HEADER_KEY])) {
            return $headers[self::HTTP_CUSTOM_AUTH_HEADER_KEY];
        }

        return null;
    }

    private function generateNonce($requestDate, $body, $key)
    {
        return hash_hmac('sha512', $requestDate . "\n" . $body, $key);
    }

    /**
     * Constant time string comparison to prevent timing attacks.
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    private function stringsEqual($a, $b)
    {
        if (strlen($a) !== strlen($b)) {
            // returning early is valid, because we compare hashes an attacker does not gain information through this
            return false;
        }

        $result = 0;

        for ($i = 0; $i < strlen($a); ++$i) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }

        return 0 === $result;
    }
}
