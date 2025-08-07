<?php

namespace App\Modules\DpdIreland\src\Responses;

/**
 * Class PreAdvice.
 */
class PreAdvice extends XmlResponse
{
    public function isNotSuccess(): bool
    {
        return ! $this->isSuccess();
    }

    public function isSuccess(): bool
    {
        return ($this->status() === 'OK') && ($this->receivedConsignmentsNumber() > 0);
    }

    public function status(): string
    {
        return $this->getAttribute('Status');
    }

    public function receivedConsignmentsNumber(): int
    {
        return (int) $this->simpleXmlArray->ReceivedConsignmentsNumber;
    }

    public function consignment(): array
    {
        return (array) $this->simpleXmlArray->Consignment;
    }

    public function trackingNumber(): string
    {
        return $this->getAttribute('TrackingNumber');
    }

    public function labelImage(): string
    {
        return $this->getAttribute('LabelImage');
    }

    public function toArray(): array
    {
        return [
            'Status' => (string) $this->simpleXmlArray->Status,
            'PreAdviceErrorCode' => (array) $this->simpleXmlArray->PreAdviceErrorCode,
            'PreAdviceErrorDetails' => $this->simpleXmlArray->PreAdviceErrorDetails ? (array) $this->simpleXmlArray->PreAdviceErrorDetails : '',
            'ReceivedConsignmentsNumber' => (int) $this->simpleXmlArray->ReceivedConsignmentsNumber,
            'Consignment' => (array) $this->simpleXmlArray->Consignment,
        ];
    }
}
