<?php

namespace zcrmsdk\crm\api\handler;

class APIHandler implements APIHandlerInterface
{
    protected $requestMethod;

    protected $urlPath;

    protected $requestHeaders;

    protected $requestParams;

    protected mixed $requestBody;

    protected $apiKey;

    protected $isBulk = false;

    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    public function getUrlPath()
    {
        return $this->urlPath;
    }

    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    public function getRequestBody()
    {
        return $this->requestBody;
    }

    public function getRequestParams()
    {
        return $this->requestParams;
    }

    public function addParam($key, $value)
    {
        if (!isset($this->requestParams[$key])) {
            $this->requestParams[$key] = [
                $value,
            ];
        } else {
            $valArray = $this->requestParams[$key];
            array_push($valArray, $value);
            $this->requestParams[$key] = $valArray;
        }
    }

    public function addHeader($key, $value)
    {
        $this->requestHeaders[$key] = $value;
    }

    public function getRequestHeadersAsMap()
    {
        return CommonUtil . convertJSONObjectToHashMap($this->requestHeaders);
    }

    public function getRequestParamsAsMap()
    {
        return CommonUtil . convertJSONObjectToHashMap($this->requestParams);
    }

    public static function getEmptyJSONObject()
    {
        return json_decode('{}');
    }

    /**
     * Set the request method.
     *
     * @param string $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * Set the request urlPath.
     *
     * @param string $urlPath
     */
    public function setUrlPath($urlPath)
    {
        $this->urlPath = $urlPath;
    }

    /**
     * set the request Headers.
     *
     * @param array $requestHeaders
     */
    public function setRequestHeaders($requestHeaders)
    {
        $this->requestHeaders = $requestHeaders;
    }

    /**
     * Set the request parameters.
     *
     * @param array $requestParams
     */
    public function setRequestParams($requestParams): void
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
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set the API Key used in the input json data(like 'modules', 'data','layouts',..etc).
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get url is bulk or not.
     *
     * @return bool|bool
     */
    public function isBulk()
    {
        return $this->isBulk;
    }
}
