<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;

class FileAPIResponse
{
    /**
     * response.
     */
    private null|string $response = null;

    /**
     * Json response.
     *
     * @var array array of json object
     */
    private array $responseJSON = [];

    /**
     * http status code.
     */
    private null|string $httpStatusCode = null;

    /**
     * response headers.
     */
    private array $responseHeaders = [];

    /**
     * code.
     */
    private null|string $code = null;

    /**
     * message.
     */
    private null|string $message = null;

    /**
     * details.
     */
    private array $details = [];

    /**
     * response status.
     */
    private null|string $status = null;

    /**
     * method to set the content of the file.
     *
     * @param string $httpResponse   http response
     * @param int    $httpStatusCode status code
     *
     * @return FileAPIResponse instance of the FileAPIResponse class containing the file api response
     *
     * @throws ZCRMException exception is thrown if the response is faulty
     */
    public function setFileContent(string $httpResponse, int $httpStatusCode): self
    {
        $this->httpStatusCode = $httpStatusCode;
        if (APIConstants::RESPONSECODE_NO_CONTENT == $httpStatusCode) {
            $this->responseJSON = [];
            $this->responseHeaders = [];
            $exception = new ZCRMException(APIConstants::INVALID_ID_MSG, $httpStatusCode);
            $exception->setExceptionCode('No Content');
            throw $exception;
        }
        list($headers, $content) = explode("\r\n\r\n", $httpResponse, 2);
        $headerArray = explode("\r\n", $headers, 50);
        $headerMap = [];
        foreach ($headerArray as $key) {
            if (strpos($key, ':')) {
                $splitArray = explode(':', $key);
                $headerMap[$splitArray[0]] = $splitArray[1];
            }
        }
        if (in_array($httpStatusCode, APIExceptionHandler::getFaultyResponseCodes())) {
            $content = json_decode($content, true);
            $this->responseJSON = $content;
            $exception = new ZCRMException($content['message'], $httpStatusCode);
            $exception->setExceptionCode($content['code']);
            $exception->setExceptionDetails($content['details']);
            throw $exception;
        } elseif (APIConstants::RESPONSECODE_OK == $httpStatusCode) {
            $this->response = $content;
            $this->responseJSON = [];
            $this->status = APIConstants::STATUS_SUCCESS;
        }
        $this->responseHeaders = $headerMap;

        return $this;
    }

    /**
     * method to get the name of the file.
     *
     * @return string the name of the file
     */
    public function getFileName(): string
    {
        $contentDisp = self::getResponseHeaders()['Content-Disposition'];
        if (null == $contentDisp) {
            $contentDisp = self::getResponseHeaders()['Content-disposition'];
        }
        $fileName = substr($contentDisp, strrpos($contentDisp, "'") + 1, strlen($contentDisp));
        if (str_contains($fileName, '=')) {
            $fileName = substr($fileName, strrpos($fileName, '=') + 1, strlen($fileName));
            $fileName = str_replace(['\'', '"'], '', $fileName);
        }

        return $fileName;
    }

    /**
     * method to get the content of the file.
     *
     * @return string content of the file
     */
    public function getFileContent(): ?string
    {
        return $this->response;
    }

    /**
     * method to get the response.
     *
     * @return string the response
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * method to set the response.
     *
     * @param string $response the reponse to be set
     */
    public function setResponse(null|string $response): void
    {
        $this->response = $response;
    }

    /**
     * method to get the json response object.
     *
     * @return array array of the Json response objects
     */
    public function getResponseJSON(): array
    {
        return $this->responseJSON;
    }

    /**
     * method to set the json response objects.
     *
     * @param array $responseJSON array of the Json response objects
     */
    public function setResponseJSON(array $responseJSON): void
    {
        $this->responseJSON = $responseJSON;
    }

    /**
     * method to get the http Status Code.
     *
     * @return string the http Status Code
     */
    public function getHttpStatusCode(): null|string
    {
        return $this->httpStatusCode;
    }

    /**
     * method to set the http Status Code.
     *
     * @param string $httpStatusCode the http Status Code
     */
    public function setHttpStatusCode(null|string $httpStatusCode): void
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * method to get the response headers.
     *
     * @return array array containing the response headers
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * method to set the response headers.
     *
     * @param array $responseHeaders array containing the response headers
     */
    public function setResponseHeaders(array $responseHeaders): void
    {
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * method to get the code.
     *
     * @return string the code
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * method to set the code.
     *
     * @param string $code the code to be set
     */
    public function setCode(null|string $code): void
    {
        $this->code = $code;
    }

    /**
     * method to get the message.
     *
     * @return string the message
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * method to set the message.
     *
     * @param string $message the message
     */
    public function setMessage(null|string $message): void
    {
        $this->message = $message;
    }

    /**
     * method to get the details.
     *
     * @return array array containing the details
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * method to set the details.
     *
     * @param array $details array containing the details
     */
    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    /**
     * method to get the status.
     *
     * @return string the status
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
}
