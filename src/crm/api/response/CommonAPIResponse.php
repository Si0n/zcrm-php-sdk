<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\utility\APIConstants;

class CommonAPIResponse
{
    /**
     * http status code.
     *
     * @var int
     */
    private null|int $httpStatusCode;

    /**
     * response Json.
     *
     * @var object
     */
    private $responseJSON;

    /**
     * response headers.
     *
     * @var array
     */
    private array $responseHeaders = [];

    /**
     * response code.
     *
     * @var string
     */
    private null|string $code = null;

    /**
     * response message.
     *
     * @var string
     */
    private $message;

    /**
     * response details.
     *
     * @var array
     */
    private array $details = [];

    /**
     * the entire response.
     *
     * @var string
     */
    private null|string $response = null;

    private null|string $apiName = null;

    public function __construct(null|string $httpResponse, null|int $httpStatusCode, null|string $apiName = null)
    {
        $this->apiName = $apiName;
        $this->response = $httpResponse;
        $this->httpStatusCode = $httpStatusCode;
        $this->setResponseJSON();
        $this->processResponse();
    }

    /**
     * method to process the api response.
     */
    public function processResponse()
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
    public function handleForFaultyResponses()
    {
        return;
    }

    /**
     * method to process the correct api response.
     */
    public function processResponseData()
    {
        return;
    }

    public function setResponseJSON()
    {
        if (APIConstants::RESPONSECODE_NO_CONTENT == $this->httpStatusCode || APIConstants::RESPONSECODE_NOT_MODIFIED == $this->httpStatusCode) {
            $this->responseJSON = [];
            $this->responseHeaders = [];

            return;
        }
        list($headers, $content) = explode("\r\n\r\n", $this->response, 2);
        $headerArray = explode("\r\n", $headers, 50);
        $headerMap = [];
        foreach ($headerArray as $key) {
            if (false != strpos($key, ':')) {
                $firstHalf = substr($key, 0, strpos($key, ':'));
                $secondHalf = substr($key, strpos($key, ':') + 1);
                $headerMap[$firstHalf] = trim($secondHalf);
            }
        }
        $jsonResponse = json_decode($content, true);
        if (null == $jsonResponse && APIConstants::RESPONSECODE_NO_CONTENT != $this->httpStatusCode) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
            $jsonResponse = json_decode($content, true);
        }
        $this->responseJSON = $jsonResponse;
        $this->responseHeaders = $headerMap;
    }

    /**
     * method to set the http status code.
     *
     * @param int $statusCode the http status code
     */
    public function setHttpStatusCode($statusCode)
    {
        $this->httpStatusCode = $statusCode;
    }

    /**
     * method to get the http status code.
     *
     * @return int the http status code
     */
    public function getHttpStatusCode()
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
    public function setResponseHeaders($responseHeader)
    {
        $this->responseHeaders = $responseHeader;
    }

    /**
     * method to get the response headers.
     *
     * @return array array of response headers
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * method to get the expiry time of the access token.
     *
     * @return string expiry time if the access token in iso8601 format
     */
    public function getExpiryTimeOfAccessToken()
    {
        return $this->responseHeaders[APIConstants::ACCESS_TOKEN_EXPIRY];
    }

    /**
     * method to get the api limit for the current window.
     *
     * @return int the api limit for the current window
     */
    public function getAPILimitForCurrentWindow()
    {
        return $this->responseHeaders[APIConstants::CURR_WINDOW_API_LIMIT];
    }

    /**
     * method to get the remaining api count for the current window.
     *
     * @return string the remaining api count for the current window
     */
    public function getRemainingAPICountForCurrentWindow()
    {
        return $this->responseHeaders[APIConstants::CURR_WINDOW_REMAINING_API_COUNT];
    }

    /**
     * method to get the reset time of the current window in milli seconds.
     *
     * @return int the reset time of the current window in milli seconds
     */
    public function getCurrentWindowResetTimeInMillis()
    {
        return $this->responseHeaders[APIConstants::CURR_WINDOW_RESET];
    }

    /**
     * method to get the remaining api count for the day.
     *
     * @return int the remaining api count for the day
     */
    public function getRemainingAPICountForTheDay()
    {
        return $this->responseHeaders[APIConstants::API_COUNT_REMAINING_FOR_THE_DAY];
    }

    /**
     * method to get the api limit of the day.
     *
     * @return string the api limit of the day
     */
    public function getAPILimitForTheDay()
    {
        return $this->responseHeaders[APIConstants::API_LIMIT_FOR_THE_DAY];
    }

    /**
     * method to Get the response code like SUCCESS,INVALID_DATA,..etc.
     *
     * @return string the response code like SUCCESS,INVALID_DATA,..etc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * method to set the response code like SUCCESS,INVALID_DATA,..etc.
     *
     * @param string $code
     *                     the response code like SUCCESS,INVALID_DATA,..etc
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * method to Get the response message.
     *
     * @return string the response message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * method to Set the response message.
     *
     * @param string $message
     *                        the response message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * method to Get the extra details of response (if any).
     *
     * @return array array containing the extra details of the response
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * method to Set the extra details for response (if any).
     *
     * @param array $details
     *                       array containing the extra details of the response
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * method to get the response.
     *
     * @return string the entire response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * methdo to set the response.
     *
     * @param string $response
     *                         the entire response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * method to get the api name.
     *
     * @return string
     */
    public function getAPIName()
    {
        return $this->apiName;
    }
}
