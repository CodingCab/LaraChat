<?php

namespace App\Modules\Api2cart\src\Api;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class RequestResponse
{
    const RETURN_CODE_OK = 0;

    const RETURN_CODE_INCORRECT_API_KEY_OR_IP_ADDRESS = 2;

    const RETURN_CODE_EXCEEDED_CONCURRENT_API_REQUESTS_PER_STORE = 7;

    const RETURN_CODE_STORE_ID_NOT_SUPPORTED = 109;

    const RETURN_CODE_MODEL_NOT_FOUND = 112;

    const RETURN_CODE_REQUIRED_PARAMETER_IS_NOT_SPECIFIED = 114;

    const RETURN_CODE_PRODUCT_SKU_MUST_BE_UNIQUE = 113;

    private ResponseInterface $response;

    private string $response_content;

    /**
     * Api2CartResponse constructor.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        // used casting because PSR-7
        // https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6
        $this->response_content = $response->getBody()->getContents();
    }

    public function getAsJson(): string
    {
        return $this->response_content;
    }

    public function getResponseRaw(): ResponseInterface
    {
        return $this->response;
    }

    public function isSuccess(): bool
    {
        return ($this->response->getStatusCode() == 200) && ($this->isReturnCodeOK());
    }

    public function isNotSuccess(): bool
    {
        return ! $this->isSuccess();
    }

    public function asArray(): array
    {
        return json_decode($this->response_content, true);
    }

    public function getReturnCode(): int
    {
        return $this->asArray()['return_code'];
    }

    public function getReturnMessage(): string
    {
        return $this->asArray()['return_message'];
    }

    public function getResult(): array
    {
        return $this->asArray()['result'];
    }

    public function isReturnCode(int $return_code): bool
    {
        return $this->getReturnCode() == $return_code;
    }

    public function isReturnCodeOK(): bool
    {
        return $this->isReturnCode(self::RETURN_CODE_OK);
    }

    public function isReturnCodeModelNotFound(): bool
    {
        return $this->isReturnCode(self::RETURN_CODE_MODEL_NOT_FOUND);
    }

    public function isReturnCodeProductSkuMustBeUnique(): bool
    {
        return $this->isReturnCode(self::RETURN_CODE_PRODUCT_SKU_MUST_BE_UNIQUE);
    }

    public function collect(): Collection
    {
        return collect($this->asArray());
    }
}
