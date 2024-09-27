<?php

namespace zcrmsdk\crm\bulkapi\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\handler\APIHandler;
use zcrmsdk\crm\bulkcrud\ZCRMBulkCallBack;
use zcrmsdk\crm\bulkcrud\ZCRMBulkResult;
use zcrmsdk\crm\bulkcrud\ZCRMBulkWriteFieldMapping;
use zcrmsdk\crm\bulkcrud\ZCRMBulkWriteFileStatus;
use zcrmsdk\crm\bulkcrud\ZCRMBulkWriteResource;
use zcrmsdk\crm\crud\ZCRMAttachment;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;
use zcrmsdk\crm\utility\ZCRMConfigUtil;

class BulkWriteAPIHandler extends APIHandler
{
    protected $record;

    private function __construct($zcrmbulkread)
    {
        $this->record = $zcrmbulkread;
    }

    public static function getInstance($zcrmbulkread)
    {
        return new BulkWriteAPIHandler($zcrmbulkread);
    }

    public function uploadFile($filePath, $headers)
    {
        try {
            if (null == $filePath) {
                throw new ZCRMException('File path must not be null for file upload operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            if (sizeof($headers) <= 0) {
                throw new ZCRMException('Headers must not be null for file upload operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = ZCRMConfigUtil::getFileUploadURL() . '/crm/' . ZCRMConfigUtil::getAPIVersion() . '/' . APIConstants::UPLOAD;
            if (null != $headers) {
                foreach ($headers as $key => $value) {
                    $this->addHeader($key, ' ' . $value); // header value with space in starting position
                }
            }
            $responseInstance = APIRequest::getInstance($this)->uploadFile($filePath);
            $responseJson = $responseInstance->getResponseJSON();
            $detailsJSON = isset($responseJson[APIConstants::DETAILS]) ? $responseJson[APIConstants::DETAILS] : [];
            $responseInstance->setData(self::getZCRMAttachmentObject($detailsJSON));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function createBulkWriteJob()
    {
        try {
            if (null != $this->record->getJobId()) {
                throw new ZCRMException('JOB ID must be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = APIConstants::WRITE;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = json_encode(self::getZCRMBulkWriteAsJSON());
            $this->isBulk = true;

            // fire request
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $reponseDetails = $responseInstance->getResponseJSON()[APIConstants::DETAILS];
            self::setBulkWriteRecordProperties($reponseDetails);
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getBulkWriteJobDetails()
    {
        try {
            if (null == $this->record->getJobId()) {
                throw new ZCRMException('JOB ID must not be null for get operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = APIConstants::WRITE . '/' . $this->record->getJobId();
            $this->addHeader('Content-Type', 'application/json');
            $this->isBulk = true;

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $reponseDetails = $responseInstance->getResponseJSON();
            self::setBulkWriteRecordProperties($reponseDetails);
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function downloadBulkWriteResult($downloadURL)
    {
        if (null == $downloadURL) {
            throw new ZCRMException('Download File URL must not be null for download operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
        $this->urlPath = $downloadURL;

        return APIRequest::getInstance($this)->downloadFile();
    }

    private function getZCRMAttachmentObject($attachmentJson)
    {
        $attachment = ZCRMAttachment::getInstance(null, isset($attachmentJson['file_id']) ? ($attachmentJson['file_id']) : '0');
        if (isset($attachmentJson['created_time'])) {
            $attachment->setCreatedTime($attachmentJson['created_time']);
        }

        return $attachment;
    }

    private function getZCRMBulkWriteAsJSON()
    {
        $recordJSON = [];
        if (null != $this->record->getCharacterEncoding()) {
            $recordJSON['character_encoding'] = $this->record->getCharacterEncoding();
        }
        if (null != $this->record->getOperation()) {
            $recordJSON['operation'] = $this->record->getOperation();
        }
        if (null != $this->record->getCallback()) {
            $callback = $this->record->getCallback();
            $callBackJSON = [];
            if (null != $callback->getUrl()) {
                $callBackJSON['url'] = $callback->getUrl();
            }
            if (null != $callback->getMethod()) {
                $callBackJSON['method'] = $callback->getMethod();
            }
            $recordJSON['callback'] = $callBackJSON;
        }
        if (null != $this->record->getResources() && sizeof($this->record->getResources()) > 0) {
            $recordJSON['resource'] = self::getZCRMBulkWriteResourceAsJSONArray();
        }

        return $recordJSON;
    }

    private function getZCRMBulkWriteResourceAsJSONArray()
    {
        $resource = [];
        foreach ($this->record->getResources() as $resourceObj) {
            array_push($resource, self::getZCRMBulkWriteResourceAsJSONObject($resourceObj));
        }

        return $resource;
    }

    private function getZCRMBulkWriteResourceAsJSONObject(ZCRMBulkWriteResource $resourceObj)
    {
        $resourceJSON = [];
        if (null != $resourceObj->getType()) {
            $resourceJSON['type'] = $resourceObj->getType();
        }
        if (null != $resourceObj->getModuleAPIName()) {
            $resourceJSON['module'] = $resourceObj->getModuleAPIName();
        }
        if (null != $resourceObj->getFileId()) {
            $resourceJSON['file_id'] = $resourceObj->getFileId();
        }
        if ('true' == $resourceObj->getIgnoreEmpty() || 'false' == $resourceObj->getIgnoreEmpty()) {
            $resourceJSON['ignore_empty'] = $resourceObj->getIgnoreEmpty();
        }
        if (null != $resourceObj->getFindBy()) {
            $resourceJSON['find_by'] = $resourceObj->getFindBy();
        }
        if (null != $resourceObj->getFieldMapping() && sizeof($resourceObj->getFieldMapping()) > 0) {
            $resourceJSON['field_mappings'] = self::getZCRMBulkWriteFieldMappingAsJSONArray($resourceObj->getFieldMapping());
        }

        return $resourceJSON;
    }

    private function getZCRMBulkWriteFieldMappingAsJSONArray($fieldMapping)
    {
        $fieldMappingArray = [];
        foreach ($fieldMapping as $fieldMappingObj) {
            array_push($fieldMappingArray, self::getZCRMBulkWriteFieldMappingJSONObject($fieldMappingObj));
        }

        return $fieldMappingArray;
    }

    private function getZCRMBulkWriteFieldMappingJSONObject(ZCRMBulkWriteFieldMapping $fieldMappingObj)
    {
        $fieldMappingJSON = [];
        if (null != $fieldMappingObj->getFieldAPIName()) {
            $fieldMappingJSON['api_name'] = $fieldMappingObj->getFieldAPIName();
        }
        if ($fieldMappingObj->getIndex() >= 0 && null != $fieldMappingObj->getIndex()) {
            $fieldMappingJSON['index'] = $fieldMappingObj->getIndex();
        }
        if (null != $fieldMappingObj->getDefaultValue() && sizeof($fieldMappingObj->getDefaultValue()) > 0) {
            $fieldMappingJSON['default_value'] = $fieldMappingObj->getDefaultValue();
        }
        if (null != $fieldMappingObj->getFindBy()) {
            $fieldMappingJSON['find_by'] = $fieldMappingObj->getFindBy();
        }
        if (null != $fieldMappingObj->getFormat()) {
            $fieldMappingJSON['format'] = $fieldMappingObj->getFormat();
        }

        return $fieldMappingJSON;
    }

    private function setBulkWriteRecordProperties($recordDetails)
    {
        foreach ($recordDetails as $key => $value) {
            if ('id' == $key && null != $value) {
                $this->record->setJobId($value);
            } elseif ('created_by' == $key && null != $value) {
                $createdBy = ZCRMUser::getInstance($value['id'], $value['name']);
                $this->record->setCreatedBy($createdBy);
            } elseif ('created_time' == $key && null != $value) {
                $this->record->setCreatedTime($value);
            } elseif ('status' == $key && null != $value) {
                $this->record->setStatus($value);
            } elseif ('character_encoding' == $key && null != $value) {
                $this->record->setCharacterEncoding($value);
            } elseif ('resource' == $key && null != $value) {
                self::setZCRMBulkWriteResourceObject($value);
            } elseif ('result' == $key && null != $value) {
                $result = ZCRMBulkResult::getInstance();
                if (isset($value['download_url'])) {
                    $result->setDownloadUrl($value['download_url']);
                }
                $this->record->setResult($result);
            } elseif ('operation' == $key && null != $value) {
                $this->record->setOperation($value);
            } elseif ('callback' == $key && null != $value) {
                $callback = ZCRMBulkCallBack::getInstance();
                if (isset($value['url'])) {
                    $callback->setUrl($value['url']);
                }
                if (isset($value['method'])) {
                    $callback->setMethod($value['method']);
                }
                $this->record->setCallback($callback);
            }
        }
    }

    private function setZCRMBulkWriteResourceObject($resource)
    {
        foreach ($resource as $resourceJSON) {
            $resourceObj = ZCRMBulkWriteResource::getInstance();
            if (isset($resourceJSON['status']) && null != $resourceJSON['status']) {
                $resourceObj->setStatus($resourceJSON['status']);
            }
            if (isset($resourceJSON['message']) && null != $resourceJSON['message']) {
                $resourceObj->setMessage($resourceJSON['message']);
            }
            if (isset($resourceJSON['type']) && null != $resourceJSON['type']) {
                $resourceObj->setType($resourceJSON['type']);
            }
            if (isset($resourceJSON['module']) && null != $resourceJSON['module']) {
                $resourceObj->setModuleAPIName($resourceJSON['module']);
            }
            if (isset($resourceJSON['field_mappings']) && null != $resourceJSON['field_mappings']) {
                foreach ($resourceJSON['field_mappings'] as $fieldMappingJSON) {
                    $resourceObj->setFieldMapping(self::setZCRMBulkWriteFieldMappingObject($fieldMappingJSON));
                }
            }
            if (isset($resourceJSON['file']) && null != $resourceJSON['file']) {
                $resourceObj->setFileStatus(self::setZCRMBulkWriteFileObject($resourceJSON['file']));
            }
            if (isset($resourceJSON['ignore_empty']) && null != $resourceJSON['ignore_empty']) {
                $resourceObj->setIgnoreEmpty($resourceJSON['ignore_empty']);
            }
            if (isset($resourceJSON['find_by']) && null != $resourceJSON['find_by']) {
                $resourceObj->setFindBy($resourceJSON['find_by']);
            }
            $this->record->setResource($resourceObj);
        }
    }

    private function setZCRMBulkWriteFieldMappingObject($fieldMappingJSON)
    {
        $fieldMappingObj = ZCRMBulkWriteFieldMapping::getInstance();
        if (isset($fieldMappingJSON['api_name']) && null != $fieldMappingJSON['api_name']) {
            $fieldMappingObj->setFieldAPIName($fieldMappingJSON['api_name']);
        }
        if (isset($fieldMappingJSON['index']) && (null != $fieldMappingJSON['index'] || 0 == $fieldMappingJSON['index'])) {
            $fieldMappingObj->setIndex($fieldMappingJSON['index']);
        }
        if (isset($fieldMappingJSON['find_by']) && null != $fieldMappingJSON['find_by']) {
            $fieldMappingObj->setFindBy($fieldMappingJSON['find_by']);
        }
        if (isset($fieldMappingJSON['format']) && null != $fieldMappingJSON['format']) {
            $fieldMappingObj->setFormat($fieldMappingJSON['format']);
        }
        if (isset($fieldMappingJSON['default_value']) && null != $fieldMappingJSON['default_value']) {
            foreach ($fieldMappingJSON['default_value'] as $fieldName => $fieldValue) {
                $fieldMappingObj->setDefaultValue($fieldName, $fieldValue);
            }
        }

        return $fieldMappingObj;
    }

    private function setZCRMBulkWriteFileObject($fileJSON)
    {
        $fileObj = ZCRMBulkWriteFileStatus::getInstance();
        if (isset($fileJSON['status']) && null != $fileJSON['status']) {
            $fileObj->setStatus($fileJSON['status']);
        }
        if (isset($fileJSON['name']) && null != $fileJSON['name']) {
            $fileObj->setFileName($fileJSON['name']);
        }
        if (isset($fileJSON['added_count']) && (null != $fileJSON['added_count'] || 0 == $fileJSON['added_count'])) {
            $fileObj->setAddedCount($fileJSON['added_count']);
        }
        if (isset($fileJSON['skipped_count']) && (null != $fileJSON['skipped_count'] || 0 == $fileJSON['skipped_count'])) {
            $fileObj->setSkippedCount($fileJSON['skipped_count']);
        }
        if (isset($fileJSON['updated_count']) && (null != $fileJSON['updated_count'] || 0 == $fileJSON['updated_count'])) {
            $fileObj->setUpdatedCount($fileJSON['updated_count']);
        }
        if (isset($fileJSON['total_count']) && (null != $fileJSON['total_count'] || 0 == $fileJSON['total_count'])) {
            $fileObj->setTotalCount($fileJSON['total_count']);
        }

        return $fileObj;
    }
}
