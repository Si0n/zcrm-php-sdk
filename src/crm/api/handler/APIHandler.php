<?php

namespace zcrmsdk\crm\api\handler;

class APIHandler implements APIHandlerInterface
{
    protected null|string $requestMethod = null;

    protected null|string $urlPath = null;

    protected null|array $requestHeaders = null;

    protected null|array $requestParams = [];

    protected mixed $requestBody = null;

    protected null|string $apiKey = null;

    protected null|bool $isBulk = false;

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

    public function addParam(int|string $key, mixed $value): void
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
    public function setRequestMethod(null|string $requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * Set the request urlPath.
     */
    public function setUrlPath(null|string $urlPath): void
    {
        $this->urlPath = $urlPath;
    }

    /**
     * set the request Headers.
     */
    public function setRequestHeaders(null|array $requestHeaders): void
    {
        $this->requestHeaders = $requestHeaders;
    }

    /**
     * Set the request parameters.
     */
    public function setRequestParams(null|array $requestParams): void
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
    public function setApiKey(null|string $apiKey): void
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
