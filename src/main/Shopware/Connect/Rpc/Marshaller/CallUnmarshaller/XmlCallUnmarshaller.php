<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Rpc\Marshaller\CallUnmarshaller;

use Shopware\Connect\Rpc\Marshaller\CallUnmarshaller;
use Shopware\Connect\Rpc;
use Shopware\Connect\Rpc\Marshaller\Converter\NoopConverter;
use Shopware\Connect\Rpc\Marshaller\Converter;
use Shopware\Connect\Rpc\Marshaller\ValueUnmarshaller\XmlValueUnmarshaller;
use Shopware\Connect\Struct\RpcCall;

class XmlCallUnmarshaller extends CallUnmarshaller
{
    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @var \Shopware\Connect\Rpc\Marshaller\ValueUnmarshaller\XmlValueUnmarshaller
     */
    private $valueUnmarshaller;

    /**
     * @param Converter|null $converter
     */
    public function __construct(Converter $converter = null)
    {
        $this->converter = $converter ?: new NoopConverter();
        $this->valueUnmarshaller = new XmlValueUnmarshaller($this->converter);
    }

    /**
     * @param string $data
     * @return \Shopware\Connect\Struct\RpcCall
     */
    public function unmarshal($data)
    {
        $this->document = $this->loadXml($data);
        return $this->unmarshalRpcCall(
            $this->document->documentElement
        );
    }

    private function unmarshalRpcCall(\DOMElement $element)
    {
        $rpcCall = new RpcCall();

        foreach ($element->childNodes as $child) {
            /** @var \DOMElement $child */
            switch($child->localName) {
                case "service":
                    $rpcCall->service = $child->textContent;
                    break;
                case "command":
                    $rpcCall->command = $child->textContent;
                    break;
                case "arguments":
                    foreach ($child->childNodes as $argument) {
                        /** @var \DOMElement $argument */
                        $rpcCall->arguments[] = $this->valueUnmarshaller->unmarshal($argument);
                    }
                    break;
                default:
                    throw new \RuntimeException("Unknown XML element: {$child->localName}.");
            }
        }

        return $rpcCall;
    }

    /**
     * @param string $data
     * @return \DOMDocument
     * @throws \UnexpectedValueException
     */
    private function loadXml($data)
    {
        if (!is_string($data) ||
            !$data) {
            throw new \UnexpectedValueException("XML string is required for unmarshalling.");
        }

        $oldErrorState = libxml_use_internal_errors(true);
        libxml_clear_errors();

        // https://bepado.atlassian.net/browse/BEP-534
        // Fix for invalid UTF-16 BOM LE at the end of the file (weird)
        $data = substr($data, strpos($data, '<'));
        $data = substr($data, 0, strrpos($data, '>')+1);

        $document = new \DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->loadXML($data, LIBXML_DTDLOAD|LIBXML_DTDATTR);
        $document->normalizeDocument();

        $errors = libxml_get_errors();
        libxml_use_internal_errors($oldErrorState);

        if (count($errors) > 0) {
            throw new \UnexpectedValueException(
                "The provided RPC XML is invalid: {$errors[0]->message}.'{$data}'"
            );
        }

        return $document;
    }
}
