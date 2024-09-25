<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;

class APIResponse extends CommonAPIResponse
{
    /**
     * data of the api response.
     *
     * @var object
     */
    private $data;

    /**
     * response status of the api.
     *
     * @var string
     */
    private null|string $status = null;


    /**
     * method to set the data of the class object.
     *
     * @param object $data data to be set for the object
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * method to get the data of the class object.
     *
     * @return object data of the object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * method to Get the response status.
     *
     * @return string the response status
     */
    public function getStatus(): null|string
    {
        return $this->status;
    }

    /**
     * method to Set the response status.
     *
     * @param string $status the response status
     */
    public function setStatus(null|string $status): void
    {
        $this->status = $status;
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
                $exception = new ZCRMException(APIConstants::INVALID_DATA . '-' . APIConstants::INVALID_ID_MSG, $statusCode);
                $exception->setExceptionCode('No Content');

                throw $exception;
            }
            $responseJSON = $this->getResponseJSON();
            $exception = new ZCRMException($responseJSON[APIConstants::MESSAGE], $statusCode);
            $exception->setExceptionCode($responseJSON[APIConstants::CODE]);
            $exception->setExceptionDetails($responseJSON[APIConstants::DETAILS]);

            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     *
     * @see CommonAPIResponse::processResponseData()
     */
    public function processResponseData(): void
    {
        $responseJSON = $this->getResponseJSON();
        if (null == $responseJSON) {
            return;
        }
        if (array_key_exists(APIConstants::DATA, $responseJSON)) {
            $responseJSON = $responseJSON[APIConstants::DATA][0];
        }
        if (array_key_exists(APIConstants::TAGS, $responseJSON)) {
            $responseJSON = $responseJSON[APIConstants::TAGS][0];
        } elseif (array_key_exists(APIConstants::USERS, $responseJSON)) {
            $responseJSON = $responseJSON[APIConstants::USERS][0];
        } elseif (array_key_exists(APIConstants::MODULES, $responseJSON)) {
            $responseJSON = $responseJSON[APIConstants::MODULES];
        } elseif (array_key_exists(APIConstants::CUSTOM_VIEWS, $responseJSON)) {
            $responseJSON = $responseJSON[APIConstants::CUSTOM_VIEWS];
        } elseif (array_key_exists(APIConstants::TAXES, $responseJSON)) {
            $responseJSON = $responseJSON[APIConstants::TAXES][0];
        } elseif (array_key_exists('variables', $responseJSON)) {
            $responseJSON = $responseJSON['variables'][0];
        }
        if (isset($responseJSON[APIConstants::STATUS]) && APIConstants::STATUS_ERROR == $responseJSON[APIConstants::STATUS]) {
            $exception = new ZCRMException($responseJSON[APIConstants::MESSAGE], $this->getHttpStatusCode());
            $exception->setExceptionCode($responseJSON[APIConstants::CODE]);
            $exception->setExceptionDetails($responseJSON[APIConstants::DETAILS]);

            throw $exception;
        }
        if (isset($responseJSON[APIConstants::STATUS]) && APIConstants::STATUS_SUCCESS == $responseJSON[APIConstants::STATUS]) {
            $this->setCode($responseJSON[APIConstants::CODE]);
            $this->setStatus($responseJSON[APIConstants::STATUS]);
            $this->setMessage($responseJSON[APIConstants::MESSAGE]);
            $this->setDetails($responseJSON[APIConstants::DETAILS]);
        }
    }
}
