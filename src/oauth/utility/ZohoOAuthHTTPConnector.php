<?php

namespace zcrmsdk\oauth\utility;

class ZohoOAuthHTTPConnector
{
    private ?string $url = null;

    private array $requestParams = [];

    private array $requestHeaders = [];

    private int $requestParamCount = 0;

    public function post(): bool|string
    {
        $curl_pointer = curl_init();
        curl_setopt($curl_pointer, CURLOPT_URL, self::getUrl());
        curl_setopt($curl_pointer, CURLOPT_HEADER, 1);
        curl_setopt($curl_pointer, CURLOPT_POSTFIELDS, self::getUrlParamsAsString($this->requestParams));
        curl_setopt($curl_pointer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_pointer, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($curl_pointer, CURLOPT_HTTPHEADER, self::getRequestHeadersAsArray());
        curl_setopt($curl_pointer, CURLOPT_POST, $this->requestParamCount);
        curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, ZohoOAuthConstants::REQUEST_METHOD_POST);
        $result = curl_exec($curl_pointer);
        curl_close($curl_pointer);

        return $result;
    }

    public function get(): bool|string
    {
        $curl_pointer = curl_init();
        $url = self::getUrl() . '?' . http_build_query($this->requestParams);
        curl_setopt($curl_pointer, CURLOPT_URL, $url);
        curl_setopt($curl_pointer, CURLOPT_HEADER, 1);
        curl_setopt($curl_pointer, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_pointer, CURLOPT_HTTPHEADER, self::getRequestHeadersAsArray());
        curl_setopt($curl_pointer, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($curl_pointer, CURLOPT_CUSTOMREQUEST, ZohoOAuthConstants::REQUEST_METHOD_GET);
        $result = curl_exec($curl_pointer);
        curl_close($curl_pointer);

        return $result;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function addParam(string $key, mixed $value): void
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

    public function getRequestHeadersMap(): array
    {
        return $this->requestHeaders;
    }

    public function getUrlParamsAsString(array $urlParams): string
    {
        $params_as_string = '';
        foreach ($urlParams as $key => $valueArray) {
            foreach ($valueArray as $value) {
                $params_as_string = $params_as_string . $key . '=' . $value . '&';
                ++$this->requestParamCount;
            }
        }
        $params_as_string = rtrim($params_as_string, '&');

        return str_replace(PHP_EOL, '', $params_as_string);
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
}
