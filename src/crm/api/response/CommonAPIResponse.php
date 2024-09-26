<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\utility\APIConstants;

class CommonAPIResponse
{
    /**
     * response Json.
     */
    protected array $responseJSON = [];

    /**
     * response headers.
     */
    protected array $responseHeaders = [];

    /**
     * response code.
     */
    protected null|string $code = null;

    /**
     * response message.
     *
     * @var string
     */
    protected mixed $message;

    /**
     * response details.
     */
    protected array $details = [];

    public function __construct(
        protected null|string $response,
        protected null|int $httpStatusCode,
        protected null|string $apiName = null
    ) {
        [$this->responseJSON, $this->responseHeaders] = self::handleCurlResponse($this->response, $this->httpStatusCode);;
        $this->processResponse();
    }

    /**
     * method to process the api response.
     */
    public function processResponse(): void
    {
        if (in_array($this->httpStatusCode, APIExceptionHandler::getFaultyResponseCodes())) {
            $this->handleForFaultyResponses();
        } elseif (APIConstants::RESPONSECODE_ACCEPTED == $this->httpStatusCode || APIConstants::RESPONSECODE_OK == $this->httpStatusCode || APIConstants::RESPONSECODE_CREATED == $this->httpStatusCode) {
            $this->processResponseData();
        }
    }

    /**
     * method to check whether any faulty api response has occured and handles it accordingly.
     */
    public function handleForFaultyResponses(): void
    {
    }

    /**
     * method to process the correct api response.
     */
    public function processResponseData(): void
    {
    }

    public static function handleCurlResponse(string $curlResponse, null|int $statusCode): array
    {
        if (in_array($statusCode, [APIConstants::RESPONSECODE_NO_CONTENT, APIConstants::RESPONSECODE_NOT_MODIFIED])) {
            return [[], []];
        }
        list($headers, $content) = explode("\r\n\r\n", $curlResponse, 2);
        $headerArray = explode("\r\n", $headers, 50);
        $headerMap = [];
        foreach ($headerArray as $key) {
            if (!strpos($key, ':')) {
                continue;
            }
            $firstHalf = substr($key, 0, strpos($key, ':'));
            $secondHalf = substr($key, strpos($key, ':') + 1);
            $headerMap[$firstHalf] = trim($secondHalf);
        }
        $jsonResponse = json_decode($content, true);
        if (null == $jsonResponse) {
            list(, $content) = explode("\r\n\r\n", $content, 2);
            $jsonResponse = json_decode($content, true);
        }

        return [
            is_array($jsonResponse) ? $jsonResponse : [],
            $headerMap,
        ];
    }

    /**
     * method to set the http status code.
     *
     * @param int $statusCode the http status code
     */
    public function setHttpStatusCode(null|int $statusCode): void
    {
        $this->httpStatusCode = $statusCode;
    }

    /**
     * method to get the http status code.
     *
     * @return int the http status code
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * method to get the json response.
     *
     * @return array array contaions the json response in key-value format
     */
    public function getResponseJSON()
    {
        return $this->responseJSON;
    }

    /**
     * method to set the response headers.
     *
     * @param array $responseHeader array of response headers
     */
    public function setResponseHeaders(array $responseHeader): void
    {
        $this->responseHeaders = $responseHeader;
    }

    /**
     * method to get the response headers.
     *
     * @return array array of response headers
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * method to get the expiry time of the access token.
     *
     * @return string expiry time if the access token in iso8601 format
     */
    public function getExpiryTimeOfAccessToken(): null|string
    {
        return $this->responseHeaders[APIConstants::ACCESS_TOKEN_EXPIRY] ?? null;
    }

    /**
     * method to get the api limit for the current window.
     *
     * @return int the api limit for the current window
     */
    public function getAPILimitForCurrentWindow(): null|int
    {
        return $this->responseHeaders[APIConstants::CURR_WINDOW_API_LIMIT] ?? null;
    }

    /**
     * method to get the remaining api count for the current window.
     *
     * @return string the remaining api count for the current window
     */
    public function getRemainingAPICountForCurrentWindow(): null|string
    {
        return $this->responseHeaders[APIConstants::CURR_WINDOW_REMAINING_API_COUNT] ?? null;
    }

    /**
     * method to get the reset time of the current window in milli seconds.
     *
     * @return int the reset time of the current window in milli seconds
     */
    public function getCurrentWindowResetTimeInMillis(): null|int
    {
        return $this->responseHeaders[APIConstants::CURR_WINDOW_RESET] ?? null;
    }

    /**
     * method to get the remaining api count for the day.
     *
     * @return int the remaining api count for the day
     */
    public function getRemainingAPICountForTheDay(): null|int
    {
        return $this->responseHeaders[APIConstants::API_COUNT_REMAINING_FOR_THE_DAY] ?? null;
    }

    /**
     * method to get the api limit of the day.
     *
     * @return string the api limit of the day
     */
    public function getAPILimitForTheDay(): null|string
    {
        return $this->responseHeaders[APIConstants::API_LIMIT_FOR_THE_DAY] ?? null;
    }

    /**
     * method to Get the response code like SUCCESS,INVALID_DATA,..etc.
     *
     * @return string the response code like SUCCESS,INVALID_DATA,..etc
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * method to set the response code like SUCCESS,INVALID_DATA,..etc.
     *
     * @param string $code
     *                     the response code like SUCCESS,INVALID_DATA,..etc
     */
    public function setCode(null|string $code): void
    {
        $this->code = $code;
    }

    /**
     * method to Get the response message.
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }

    /**
     * method to Set the response message.
     *
     * @param string $message
     *                        the response message
     */
    public function setMessage(mixed $message): void
    {
        $this->message = $message;
    }

    /**
     * method to Get the extra details of response (if any).
     *
     * @return array array containing the extra details of the response
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * method to Set the extra details for response (if any).
     *
     * @param array $details
     *                       array containing the extra details of the response
     */
    public function setDetails(null|array $details): void
    {
        $this->details = $details ?? [];
    }

    /**
     * method to get the response.
     *
     * @return string the entire response
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * methdo to set the response.
     *
     * @param string $response
     *                         the entire response
     */
    public function setResponse(null|string $response): void
    {
        $this->response = $response;
    }

    /**
     * method to get the api name.
     */
    public function getAPIName(): ?string
    {
        return $this->apiName;
    }
}
