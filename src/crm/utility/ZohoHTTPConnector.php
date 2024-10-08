<?php

namespace zcrmsdk\crm\utility;

use zcrmsdk\crm\api\response\CommonAPIResponse;

/**
 * Purpose of this class is to trigger API call and fetch the response.
 *
 * @author sumanth-3058
 */
class ZohoHTTPConnector
{
    private null|string $url = null;

    private null|array $requestParams = [];

    private null|array $requestHeaders = [];

    private null|int $requestParamCount = 0;

    private mixed $requestBody = null;

    private null|string $requestType = APIConstants::REQUEST_METHOD_GET;

    private null|string $userAgent = 'ZohoCRM PHP SDK';

    private null|string $apiKey = null;

    private null|bool $isBulkRequest = false;

    private function __construct()
    {
    }

    public static function getInstance(): ZohoHTTPConnector
    {
        return new ZohoHTTPConnector();
    }

    public function fireRequest(): array
    {
        $curl_pointer = curl_init();
        if (is_array(self::getRequestParamsMap()) && count(self::getRequestParamsMap()) > 0) {
            $url = self::getUrl() . '?' . self::getUrlParamsAsString(self::getRequestParamsMap());
            curl_setopt($curl_pointer, CURLOPT_URL, $url);
        } else {
            $url = self::getUrl();
        }

        curl_setopt($curl_pointer, CURLOPT_URL, $url);
        curl_setopt($curl_pointer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_pointer, CURLOPT_HEADER, 1);
        curl_setopt($curl_pointer, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl_pointer, CURLOPT_HTTPHEADER, $requestHeaders = self::getRequestHeadersAsArray());
        curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_GET);

        $requestPostBody = [];
        if (APIConstants::REQUEST_METHOD_POST === $this->requestType) {
            curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_POST);
            curl_setopt($curl_pointer, CURLOPT_POST, true);
            curl_setopt($curl_pointer, CURLOPT_POSTFIELDS, $requestPostBody = $this->isBulkRequest() ? json_encode(self::getRequestBody()) : self::getRequestBody());
        } elseif (APIConstants::REQUEST_METHOD_PUT === $this->requestType) {
            curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_PUT);
            curl_setopt($curl_pointer, CURLOPT_POSTFIELDS, $requestPostBody = $this->isBulkRequest() ? json_encode(self::getRequestBody()) : self::getRequestBody());
        } elseif (APIConstants::REQUEST_METHOD_DELETE === $this->requestType) {
            curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, APIConstants::REQUEST_METHOD_DELETE);
        }
        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);

        [$content, $headers] = CommonAPIResponse::handleCurlResponse($result, $responseInfo[APIConstants::HTTP_CODE]);

        LogManager::info(sprintf('Request %s %s', $this->requestType, $url), [
            'requestHeaders' => $requestHeaders,
            'requestPostBody' => $requestPostBody,
            'responseInfo' => ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOG_RESPONSE_INFO) ? $responseInfo : '<logging disabled>',
            'responseHeaders' => ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOG_RESPONSE_HEADERS) ? $headers : '<logging disabled>',
            'responseContent' => ZCRMConfigUtil::getConfigValue(APIConstants::APPLICATION_LOG_RESPONSE_BODY) ? $content : '<logging disabled>',
        ]);

        return [
            $result,
            $responseInfo,
        ];
    }

    public function downloadFile(): array
    {
        $curl_pointer = curl_init();
        curl_setopt($curl_pointer, CURLOPT_URL, self::getUrl());
        curl_setopt($curl_pointer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_pointer, CURLOPT_HEADER, 1);
        curl_setopt($curl_pointer, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl_pointer, CURLOPT_HTTPHEADER, self::getRequestHeadersAsArray());
        // curl_setopt($curl_pointer,CURLOPT_SSLVERSION,3);
        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);

        return [
            $result,
            $responseInfo,
        ];
    }

    public function getUrl(): null|string
    {
        return $this->url;
    }

    public function setUrl(null|string $url): void
    {
        $this->url = $url;
    }

    public function addParam(int|string $key, mixed $value): void
    {
        if (null == $this->requestParams[$key]) {
            $this->requestParams[$key] = [
                $value,
            ];
        } else {
            $valArray = $this->requestParams[$key];
            $valArray[] = $value;
            $this->requestParams[$key] = $valArray;
        }
    }

    public function addHeader(string $key, int|float|string $value): void
    {
        if (null == $this->requestHeaders[$key]) {
            $this->requestHeaders[$key] = [
                $value,
            ];
        } else {
            $valArray = $this->requestHeaders[$key];
            $valArray[] = $value;
            $this->requestHeaders[$key] = $valArray;
        }
    }

    public function getUrlParamsAsString(iterable $urlParams): array|string
    {
        $params_as_string = '';
        foreach ($urlParams as $key => $valueArray) {
            foreach ($valueArray as $value) {
                $params_as_string = $params_as_string . $key . '=' . urlencode($value) . '&';
                ++$this->requestParamCount;
            }
        }
        $params_as_string = rtrim($params_as_string, '&');

        return str_replace(PHP_EOL, '', $params_as_string);
    }

    public function setRequestHeadersMap(null|array $headers): void
    {
        $this->requestHeaders = $headers;
    }

    public function getRequestHeadersMap(): null|array
    {
        return $this->requestHeaders;
    }

    public function setRequestParamsMap(null|array $params): void
    {
        $this->requestParams = $params;
    }

    public function getRequestParamsMap(): null|array
    {
        return $this->requestParams;
    }

    public function setRequestBody(mixed $reqBody): void
    {
        $this->requestBody = $reqBody;
    }

    public function getRequestBody(): mixed
    {
        return $this->requestBody;
    }

    public function setRequestType(null|string $reqType): void
    {
        $this->requestType = $reqType;
    }

    public function getRequestType(): null|string
    {
        return $this->requestType;
    }

    public function getRequestHeadersAsArray(): array
    {
        $headersArray = [];
        $headersMap = self::getRequestHeadersMap();
        foreach ($headersMap as $key => $value) {
            $headersArray[] = $key . ':' . $value;
        }

        return $headersArray;
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
     * isBulkRequest.
     */
    public function isBulkRequest(): null|bool
    {
        return $this->isBulkRequest;
    }

    /**
     * isBulkRequest.
     *
     * @param
     *            $isBulkRequest
     */
    public function setBulkRequest(null|bool $isBulkRequest): void
    {
        $this->isBulkRequest = $isBulkRequest;
    }
}
