<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;

class BulkAPIResponse extends CommonAPIResponse
{
    /**
     * the bulk data.
     *
     * @var array
     */
    private $bulkData;

    /**
     * response status of the api.
     *
     * @var string
     */
    private $status;

    /**
     * the response information.
     *
     * @var ResponseInfo
     */
    private $info;

    /**
     * bulk entities response.
     *
     * @var array array of EntityResponse instances
     */
    private $bulkEntitiesResponse;

    /**
     * constructor to set the http response and http status code.
     *
     * @param string $httpResponse   the http response
     * @param int    $httpStatusCode http status code
     */
    public function __construct($httpResponse, $httpStatusCode)
    {
        parent::__construct($httpResponse, $httpStatusCode);
        $this->setInfo();
    }

    /**
     * @throws ZCRMException
     *
     * @see CommonAPIResponse::handleForFaultyResponses()
     */
    public function handleForFaultyResponses(): void
    {
        $statusCode = self::getHttpStatusCode();
        if (in_array($statusCode, APIExceptionHandler::getFaultyResponseCodes())) {
            if (APIConstants::RESPONSECODE_NO_CONTENT == $statusCode) {
                $exception = new ZCRMException('No Content', $statusCode);
                $exception->setExceptionCode('NO CONTENT');
                throw $exception;
            }
            if (APIConstants::RESPONSECODE_NOT_MODIFIED == $statusCode) {
                $exception = new ZCRMException('Not Modified', $statusCode);
                $exception->setExceptionCode('NOT MODIFIED');
                throw $exception;
            }
            $responseJSON = $this->getResponseJSON();
            $exception = new ZCRMException($responseJSON[APIConstants::MESSAGE] ?? 'unknown', $statusCode);
            $exception->setExceptionCode($responseJSON[APIConstants::CODE]);
            $exception->setExceptionDetails($responseJSON[APIConstants::DETAILS]);
            throw $exception;
        }
    }

    /**
     * @see CommonAPIResponse::processResponseData()
     */
    public function processResponseData(): void// status of the response
    {
        $this->bulkEntitiesResponse = [];
        $bulkResponseJSON = $this->getResponseJSON();
        foreach ([APIConstants::DATA, APIConstants::TAGS, APIConstants::TAXES, APIConstants::VARIABLES] as $key) {
            if (array_key_exists($key, $bulkResponseJSON)) {
                $recordsArray = $bulkResponseJSON[$key];
                foreach ($recordsArray as $record) {
                    if (null != $record && array_key_exists(APIConstants::STATUS, $record)) {
                        $this->bulkEntitiesResponse[] = new EntityResponse($record);
                    }
                }
            }
        }
    }

    /**
     * method to get the bulk data.
     *
     * @return array array of data instances
     */
    public function getData()
    {
        return $this->bulkData;
    }

    /**
     * method to set the bulk data.
     *
     * @param array $bulkData array of data instances
     */
    public function setData($bulkData)
    {
        $this->bulkData = $bulkData;
    }

    /**
     * method to Get the response status.
     *
     * @return string the response status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * method to Set the response status.
     *
     * @param string $status the response status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * method to get the response information.
     *
     * @return ResponseInfo instance of the ResponseInfo class
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * method to set the response information.
     */
    public function setInfo(): void
    {
        if (array_key_exists(APIConstants::INFO, $this->getResponseJSON())) {
            $this->info = new ResponseInfo($this->getResponseJSON()[APIConstants::INFO]);
        }
    }

    /**
     * method to get the bulk entity responses.
     *
     * @return array array of the instances of EntityResponse class
     */
    public function getEntityResponses()
    {
        return $this->bulkEntitiesResponse;
    }
}
