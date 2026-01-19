<?php

namespace zcrmsdk\crm\api\handler;

class APIHandler implements APIHandlerInterface
{
    protected ?string $requestMethod = null;

    protected ?string $urlPath = null;

    protected ?array $requestHeaders = null;

    protected ?array $requestParams = [];

    protected mixed $requestBody = null;

    protected ?string $apiKey = null;

    protected ?bool $isBulk = false;

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function getUrlPath(): ?string
    {
        return $this->urlPath;
    }

    public function getRequestHeaders(): ?array
    {
        return $this->requestHeaders;
    }

    public function getRequestBody(): mixed
    {
        return $this->requestBody;
    }

    public function getRequestParams(): ?array
    {
        return $this->requestParams;
    }

    public function addParam(int | string $key, mixed $value): void
    {
        if (!isset($this->requestParams[$key])) {
            $this->requestParams[$key] = [
                $value,
            ];
        } else {
            $valArray = $this->requestParams[$key];
            $valArray[] = $value;
            $this->requestParams[$key] = $valArray;
        }
    }

    public function addHeader(string $key, mixed $value): void
    {
        $this->requestHeaders[$key] = $value;
    }

    public function getRequestHeadersAsMap(): ?array
    {
        return $this->requestHeaders;
    }

    public function getRequestParamsAsMap(): ?array
    {
        return $this->requestParams;
    }

    public static function getEmptyJSONObject(): mixed
    {
        return json_decode('{}');
    }

    /**
     * Set the request method.
     */
    public function setRequestMethod(?string $requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * Set the request urlPath.
     */
    public function setUrlPath(?string $urlPath): void
    {
        $this->urlPath = $urlPath;
    }

    /**
     * set the request Headers.
     */
    public function setRequestHeaders(?array $requestHeaders): void
    {
        $this->requestHeaders = $requestHeaders;
    }

    /**
     * Set the request parameters.
     */
    public function setRequestParams(?array $requestParams): void
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Set the requestBody.
     */
    public function setRequestBody(mixed $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    /**
     * Get the API Key used in the input json data(like 'modules', 'data','layouts',..etc).
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Set the API Key used in the input json data(like 'modules', 'data','layouts',..etc).
     */
    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get url is bulk or not.
     *
     * @return bool|bool
     */
    public function isBulk(): ?bool
    {
        return $this->isBulk;
    }
}
