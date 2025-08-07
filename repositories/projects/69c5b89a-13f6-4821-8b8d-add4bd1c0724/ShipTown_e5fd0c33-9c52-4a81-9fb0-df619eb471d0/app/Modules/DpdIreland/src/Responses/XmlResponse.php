<?php

namespace App\Modules\DpdIreland\src\Responses;

use Illuminate\Support\Str;
use SimpleXMLElement;

/**
 * Class PreAdviceResponse.
 */
abstract class XmlResponse
{
    /**
     * @var string
     */
    private $xml;

    /**
     * @var SimpleXMLElement|string
     */
    protected $simpleXmlArray;

    /**
     * PreAdviceResponse constructor.
     */
    public function __construct(string $responseXml)
    {
        $this->setXml($responseXml);
    }

    public function setXml(string $responseXml): XmlResponse
    {
        $this->xml = $responseXml;

        $this->simpleXmlArray = simplexml_load_string($this->xml);

        return $this;
    }

    public function toString(): string
    {
        return $this->xml;
    }

    public function getAttribute($key): string
    {
        $startTag = "<$key>";
        $endTag = "</$key>";

        return Str::before(Str::after($this->xml, $startTag), $endTag);
    }

    public function getPreAdviceErrorCode(): string
    {
        return $this->simpleXmlArray->PreAdviceErrorCode;
    }

    public function getPreAdviceErrorDetails(): string
    {
        return $this->simpleXmlArray->PreAdviceErrorDetails;
    }
}
