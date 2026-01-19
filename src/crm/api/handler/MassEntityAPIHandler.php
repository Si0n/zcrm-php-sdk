<?php

namespace zcrmsdk\crm\api\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\crud\ZCRMTrashRecord;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;

class MassEntityAPIHandler extends APIHandler
{
    public function __construct(protected ZCRMModule $module)
    {
    }

    public static function getInstance(ZCRMModule $moduleInstance): MassEntityAPIHandler
    {
        return new MassEntityAPIHandler($moduleInstance);
    }

    /**
     * @throws ZCRMException
     */
    public function createRecords(array $records, ?array $trigger = null, ?string $lar_id = null, ?string $layoutId = null): BulkAPIResponse
    {
        if (count($records) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->urlPath = $this->module->getAPIName();
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->addHeader('Content-Type', 'application/json');
        $requestBodyObj = [];
        $dataArray = [];
        foreach ($records as $record) {
            if (null !== $record->getEntityId()) {
                throw new ZCRMException('Entity ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $dataArray[] = EntityAPIHandler::getInstance($record)->getZCRMRecordAsJSON();
        }
        $requestBodyObj['data'] = $dataArray;
        if (!empty($trigger)) {
            $requestBodyObj['trigger'] = $trigger;
        }
        if (!empty($lar_id)) {
            $requestBodyObj['lar_id'] = $lar_id;
        }
        if (!empty($layoutId)) {
            $requestBodyObj['Layout'] = ['id' => $layoutId];
        }

        $this->setRequestBody($requestBodyObj);

        // Fire Request
        $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();
        $createdRecords = [];
        $responses = $bulkAPIResponse->getEntityResponses();
        foreach ($responses as $i => $entityResIns) {
            if (APIConstants::STATUS_SUCCESS !== $entityResIns->getStatus()) {
                $entityResIns->setData(null);

                continue;
            }
            $responseData = $entityResIns->getResponseJSON();
            $recordDetails = $responseData['details'];
            $newRecord = $records[$i];
            EntityAPIHandler::getInstance($newRecord)->setRecordProperties($recordDetails);
            $createdRecords[] = $newRecord;
            $entityResIns->setData($newRecord);
        }
        $bulkAPIResponse->setData($createdRecords);

        return $bulkAPIResponse;
    }

    /**
     * @throws ZCRMException
     */
    public function upsertRecords(array $records, ?array $trigger = null, ?string $lar_id = null, ?array $duplicate_check_fields = null): BulkAPIResponse
    {
        if (count($records) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->urlPath = $this->module->getAPIName() . '/upsert';
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->addHeader('Content-Type', 'application/json');
        if (!empty($duplicate_check_fields)) {
            $this->addParam('duplicate_check_fields', implode(',', $duplicate_check_fields));
        }

        $requestBodyObj = [];
        $dataArray = [];
        foreach ($records as $record) {
            $recordJSON = EntityAPIHandler::getInstance($record)->getZCRMRecordAsJSON();
            if (null !== $record->getEntityId()) {
                $recordJSON['id'] = $record->getEntityId();
            }
            $dataArray[] = $recordJSON;
        }
        $requestBodyObj['data'] = $dataArray;
        if (!empty($trigger)) {
            $requestBodyObj['trigger'] = $trigger;
        }
        if (!empty($lar_id)) {
            $requestBodyObj['lar_id'] = $lar_id;
        }
        $this->setRequestBody($requestBodyObj);

        // Fire Request
        $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();
        $upsertRecords = [];
        $responses = $bulkAPIResponse->getEntityResponses();

        foreach ($responses as $i => $entityResIns) {
            if (APIConstants::STATUS_SUCCESS !== $entityResIns->getStatus()) {
                $entityResIns->setData(null);

                continue;
            }
            $responseData = $entityResIns->getResponseJSON();
            $recordDetails = $responseData['details'];
            $newRecord = $records[$i];
            EntityAPIHandler::getInstance($newRecord)->setRecordProperties($recordDetails);
            $upsertRecords[] = $newRecord;
            $entityResIns->setData($newRecord);
        }
        $bulkAPIResponse->setData($upsertRecords);

        return $bulkAPIResponse;
    }

    /**
     * @throws ZCRMException
     */
    public function updateRecords(array $records, ?array $trigger = null): BulkAPIResponse
    {
        if (count($records) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->urlPath = $this->module->getAPIName();
        $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
        $this->addHeader('Content-Type', 'application/json');
        $requestBodyObj = [];
        $dataArray = [];
        foreach ($records as $record) {
            $recordJSON = EntityAPIHandler::getInstance($record)->getZCRMRecordAsJSON();
            if (null !== $record->getEntityId()) {
                $recordJSON['id'] = $record->getEntityId();
            }
            $dataArray[] = $recordJSON;
        }
        $requestBodyObj['data'] = $dataArray;
        if (!empty($trigger)) {
            $requestBodyObj['trigger'] = $trigger;
        }

        $this->requestBody = $requestBodyObj;

        // Fire Request
        $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();
        $upsertRecords = [];
        $responses = $bulkAPIResponse->getEntityResponses();
        foreach ($responses as $i => $entityResIns) {
            if (APIConstants::STATUS_SUCCESS !== $entityResIns->getStatus()) {
                $entityResIns->setData(null);

                continue;
            }
            $responseData = $entityResIns->getResponseJSON();
            $recordDetails = $responseData['details'];
            $newRecord = $records[$i];
            EntityAPIHandler::getInstance($newRecord)->setRecordProperties($recordDetails);
            $upsertRecords[] = $newRecord;
            $entityResIns->setData($newRecord);
        }
        $bulkAPIResponse->setData($upsertRecords);

        return $bulkAPIResponse;
    }

    /**
     * @throws ZCRMException
     */
    public function deleteRecords(array $entityIds): BulkAPIResponse
    {
        if (count($entityIds) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->urlPath = $this->module->getAPIName();
        $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
        $this->addHeader('Content-Type', 'application/json');
        $this->addParam('ids', implode(',', $entityIds)); // converts array to string with specified seperator

        // Fire Request
        $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();
        $responses = $bulkAPIResponse->getEntityResponses();

        foreach ($responses as $entityResIns) {
            $responseData = $entityResIns->getResponseJSON();
            $responseJSON = $responseData['details'];
            $record = ZCRMRecord::getInstance($this->module->getAPIName(), $responseJSON['id']);
            $entityResIns->setData($record);
        }

        return $bulkAPIResponse;
    }

    /**
     * @throws ZCRMException
     */
    public function getAllDeletedRecords($param_map, $header_map): BulkAPIResponse
    {
        return self::getDeletedRecords($param_map, $header_map, 'all');
    }

    /**
     * @throws ZCRMException
     */
    public function getRecycleBinRecords($param_map, $header_map): BulkAPIResponse
    {
        return self::getDeletedRecords($param_map, $header_map, 'recycle');
    }

    /**
     * @throws ZCRMException
     */
    public function getPermanentlyDeletedRecords($param_map, $header_map): BulkAPIResponse
    {
        return self::getDeletedRecords($param_map, $header_map, 'permanent');
    }

    /**
     * @throws ZCRMException
     */
    private function getDeletedRecords(array $param_map, array $header_map, string $type): BulkAPIResponse
    {
        try {
            $this->urlPath = $this->module->getAPIName() . '/deleted';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            foreach ($param_map as $key => $value) {
                if (null === $value) {
                    continue;
                }
                $this->addParam($key, $value);
            }
            foreach ($header_map as $key => $value) {
                if (null === $value) {
                    continue;
                }
                $this->addHeader($key, $value);
            }
            $this->addHeader('Content-Type', 'application/json');
            $this->addParam('type', $type);
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $trashRecords = $responseJSON['data'];
            $trashRecordList = [];
            foreach ($trashRecords as $trashRecord) {
                $trashRecordInstance = ZCRMTrashRecord::getInstance($trashRecord['type'], $trashRecord['id']);
                self::setTrashRecordProperties($trashRecordInstance, $trashRecord);
                $trashRecordList[] = $trashRecordInstance;
            }

            $responseInstance->setData($trashRecordList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function setTrashRecordProperties(ZCRMTrashRecord $trashRecordInstance, array $recordProperties): void
    {
        if (null != $recordProperties['display_name']) {
            $trashRecordInstance->setDisplayName($recordProperties['display_name']);
        }
        if (null != $recordProperties['created_by']) {
            $createdBy = $recordProperties['created_by'];
            $createdBy_User = ZCRMUser::getInstance($createdBy['id'], $createdBy['name']);
            $trashRecordInstance->setCreatedBy($createdBy_User);
        }
        if (null != $recordProperties['deleted_by']) {
            $deletedBy = $recordProperties['deleted_by'];
            $deletedBy_User = ZCRMUser::getInstance($deletedBy['id'], $deletedBy['name']);
            $trashRecordInstance->setDeletedBy($deletedBy_User);
        }
        $trashRecordInstance->setDeletedTime($recordProperties['deleted_time']);
    }

    /**
     * @throws ZCRMException
     */
    public function getRecords(array $param_map, array $header_map): BulkAPIResponse
    {
        try {
            $this->urlPath = $this->module->getAPIName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            foreach ($param_map as $key => $value) {
                if (null === $value) {
                    continue;
                }
                $this->addParam($key, $value);
            }
            foreach ($header_map as $key => $value) {
                if (null === $value) {
                    continue;
                }
                $this->addHeader($key, $value);
            }
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $records = $responseJSON['data'];
            $recordsList = [];
            foreach ($records as $record) {
                $recordInstance = ZCRMRecord::getInstance($this->module->getAPIName(), $record['id']);
                EntityAPIHandler::getInstance($recordInstance)->setRecordProperties($record);
                $recordsList[] = $recordInstance;
            }
            $responseInstance->setData($recordsList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function searchRecords(array $param_map, string $type, mixed $search_value): BulkAPIResponse
    {
        try {
            $this->urlPath = $this->module->getAPIName() . '/search';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $exclusion_array = ['word', 'phone', 'email', 'criteria'];
            foreach ($exclusion_array as $exclusion) {
                if (array_key_exists($exclusion, $param_map)) {
                    unset($param_map[$exclusion]);
                }
            }
            foreach ($param_map as $key => $value) {
                if (null === $value) {
                    continue;
                }
                $this->addParam($key, $value);
            }
            $this->addParam($type, $search_value);
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $records = $responseJSON['data'];
            $recordsList = [];
            foreach ($records as $record) {
                $recordInstance = ZCRMRecord::getInstance($this->module->getAPIName(), $record['id']);
                EntityAPIHandler::getInstance($recordInstance)->setRecordProperties($record);
                $recordsList[] = $recordInstance;
            }

            $responseInstance->setData($recordsList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function massUpdateRecords(array $idList, string $apiName, mixed $value): BulkAPIResponse
    {
        if (count($idList) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $inputJSON = self::constructJSONForMassUpdate($idList, $apiName, $value);
            $this->urlPath = $this->module->getAPIName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $inputJSON;
            $this->apiKey = 'data';
            $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();

            $updatedRecords = [];
            $responses = $bulkAPIResponse->getEntityResponses();
            $size = sizeof($responses);
            for ($i = 0; $i < $size; ++$i) {
                $entityResIns = $responses[$i];
                if (APIConstants::STATUS_SUCCESS === $entityResIns->getStatus()) {
                    $responseData = $entityResIns->getResponseJSON();
                    $recordJSON = $responseData['details'];

                    $updatedRecord = ZCRMRecord::getInstance($this->module->getAPIName(), $recordJSON['id']);
                    EntityAPIHandler::getInstance($updatedRecord)->setRecordProperties($recordJSON);
                    $updatedRecords[] = $updatedRecord;
                    $entityResIns->setData($updatedRecord);
                } else {
                    $entityResIns->setData(null);
                }
            }
            $bulkAPIResponse->setData($updatedRecords);

            return $bulkAPIResponse;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function constructJSONForMassUpdate(array $idList, string $apiName, mixed $value): array
    {
        $massUpdateArray = [];
        foreach ($idList as $id) {
            $updateJson = [];
            $updateJson['id'] = '' . $id;
            $updateJson[$apiName] = $value;
            $massUpdateArray[] = $updateJson;
        }

        return [
            'data' => $massUpdateArray,
        ];
    }
}
