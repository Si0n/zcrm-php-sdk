<?php

namespace zcrmsdk\crm\api\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\FileAPIResponse;
use zcrmsdk\crm\crud\ZCRMAttachment;
use zcrmsdk\crm\crud\ZCRMModuleRelation;
use zcrmsdk\crm\crud\ZCRMNote;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;
use zcrmsdk\crm\utility\CommonUtil;

class RelatedListAPIHandler extends APIHandler
{
    private $parentRecord;

    // ZCRMRecord
    private $relatedList;

    // ZCRMModuleRelation
    private $junctionRecord;

    // ZCRMJunctionRecord
    private function __construct($parentRecord, $relatedList)
    {
        $this->parentRecord = $parentRecord;
        if ($relatedList instanceof ZCRMModuleRelation) {
            $this->relatedList = $relatedList;
        } else {
            $this->junctionRecord = $relatedList;
        }
    }

    public static function getInstance($parentRecord, $relatedList)
    {
        return new RelatedListAPIHandler($parentRecord, $relatedList);
    }

    public function getRecords($param_map, $header_map)
    {
        try {
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            foreach ($param_map as $key => $value) {
                if (null != $value) {
                    $this->addParam($key, $value);
                }
            }
            foreach ($header_map as $key => $value) {
                if (null != $value) {
                    $this->addHeader($key, $value);
                }
            }
            $this->addHeader('Content-Type', 'application/json');

            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $records = $responseJSON['data'];
            $recordsList = [];
            foreach ($records as $record) {
                $recordInstance = ZCRMRecord::getInstance($this->relatedList->getApiName(), $record['id']);
                EntityAPIHandler::getInstance($recordInstance)->setRecordProperties($record);
                array_push($recordsList, $recordInstance);
            }

            $responseInstance->setData($recordsList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getNotes($param_map, $header_map)
    {
        try {
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            foreach ($param_map as $key => $value) {
                if (null != $value) {
                    $this->addParam($key, $value);
                }
            }
            foreach ($header_map as $key => $value) {
                if (null != $value) {
                    $this->addHeader($key, $value);
                }
            }
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $notes = $responseJSON['data'];
            $notesList = [];
            foreach ($notes as $note) {
                array_push($notesList, self::getZCRMNote($note, null));
            }

            $responseInstance->setData($notesList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getAttachments($param_map)
    {
        try {
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            foreach ($param_map as $key => $value) {
                if (null != $value) {
                    $this->addParam($key, $value);
                }
            }
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $attachments = $responseJSON['data'];
            $attachmentList = [];
            foreach ($attachments as $attachment) {
                array_push($attachmentList, self::getZCRMAttachment($attachment));
            }

            $responseInstance->setData($attachmentList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function addNotes($noteInstances)
    {
        if (sizeof($noteInstances) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_NOTES_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $dataArray = [];
            foreach ($noteInstances as $noteInstance) {
                if (null == $noteInstance->getId()) {
                    array_push($dataArray, $this->getZCRMNoteAsJSON($noteInstance));
                } else {
                    throw new ZCRMException(' ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
            }
            $requestBodyObj = [];
            $requestBodyObj['data'] = $dataArray;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $requestBodyObj;
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function addNote($zcrmNote)
    {
        try {
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->addHeader('Content-Type', 'application/json');
            $inputJSON = [
                'data' => [
                    self::getZCRMNoteAsJSON($zcrmNote),
                ],
            ];
            $this->requestBody = json_encode($inputJSON);

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $responseData = $responseJSON['data'][0];
            $responseDetails = $responseData['details'] ?? [];
            $zcrmNote = self::getZCRMNote($responseDetails, $zcrmNote);

            $responseInstance->setData($zcrmNote);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function updateNote($zcrmNote)
    {
        try {
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName() . '/' . $zcrmNote->getId();
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->addHeader('Content-Type', 'application/json');
            $inputJSON = [
                'data' => [
                    self::getZCRMNoteAsJSON($zcrmNote),
                ],
            ];
            $this->requestBody = json_encode($inputJSON);

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $responseData = $responseJSON['data'][0];
            $responseDetails = $responseData['details'] ?? [];
            $zcrmNote = self::getZCRMNote($responseDetails, $zcrmNote);
            $responseInstance->setData($zcrmNote);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteNote($zcrmNote)
    {
        try {
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName() . '/' . $zcrmNote->getId();
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->addHeader('Content-Type', 'application/json');

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function uploadAttachment(string $filePath): APIResponse
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();

            $responseInstance = APIRequest::getInstance($this)->uploadFile($filePath);
            $responseJson = $responseInstance->getResponseJSON();
            $detailsJSON = $responseJson['data'][0]['details'] ?? [];
            $responseInstance->setData(ZCRMAttachment::getInstance($this->parentRecord, isset($detailsJSON['id']) ? ($detailsJSON['id']) : '0'));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function uploadLinkAsAttachment(string $attachmentUrl, ?string $title = null): APIResponse
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName();

            $responseInstance = APIRequest::getInstance($this)->uploadLinkAsAttachment($attachmentUrl, $title);
            $responseJson = $responseInstance->getResponseJSON();
            $detailsJSON = $responseJson['data'][0]['details'] ?? [];

            $responseInstance->setData(ZCRMAttachment::getInstance($this->parentRecord, isset($detailsJSON['id']) ? ($detailsJSON['id']) : '0'));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function downloadAttachment(string $attachmentId): FileAPIResponse
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName() . '/' . $attachmentId;

            return APIRequest::getInstance($this)->downloadFile();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteAttachment(string $attachmentId): APIResponse
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->relatedList->getApiName() . '/' . $attachmentId;

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function addRelation()
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->junctionRecord->getApiName() . '/' . $this->junctionRecord->getId();

            $dataArray = $this->junctionRecord->getRelatedDetails();
            if (0 == sizeof($dataArray)) {
                $dataArray = CommonUtil::getEmptyJSONObject();
            }
            $inputJSON = [
                'data' => [
                    $dataArray,
                ],
            ];
            $this->requestBody = json_encode($inputJSON);

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function removeRelation()
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->urlPath = $this->parentRecord->getModuleApiName() . '/' . $this->parentRecord->getEntityId() . '/' . $this->junctionRecord->getApiName() . '/' . $this->junctionRecord->getId();

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getZCRMNoteAsJSON($noteIns)
    {
        $noteJson = [];
        if (null != $noteIns->getTitle()) {
            $noteJson['Note_Title'] = $noteIns->getTitle();
        }
        if (null != $noteIns->getParentModule()) {
            $noteJson['se_module'] = $noteIns->getParentModule();
        }
        if (null != $noteIns->getParentId()) {
            $noteJson['Parent_Id'] = $noteIns->getParentId();
        }
        if (null != $noteIns->getTitle()) {
            $noteJson['Note_Title'] = $noteIns->getTitle();
        }
        $noteJson['Note_Content'] = $noteIns->getContent();

        return $noteJson;
    }

    public function getZCRMNote($noteDetails, $noteIns)
    {
        if (null == $noteIns) {
            $noteIns = ZCRMNote::getInstance($this->parentRecord, $noteDetails['id']);
        }
        $noteIns->setId($noteDetails['id'] ?? null);
        $noteIns->setTitle($noteDetails['Note_Title'] ?? null);
        $noteIns->setContent($noteDetails['Note_Content'] ?? null);
        if (isset($noteDetails['Owner'])) {
            $ownerObj = $noteDetails['Owner'];
            $ownerIns = ZCRMUser::getInstance($ownerObj['id'], $ownerObj['name']);
            $noteIns->setOwner($ownerIns);
        }
        $createdByObj = $noteDetails['Created_By'];
        $createdBy = ZCRMUser::getInstance($createdByObj['id'], $createdByObj['name']);
        $noteIns->setCreatedBy($createdBy);
        $modifiedByObj = $noteDetails['Modified_By'];
        $modifiedBy = ZCRMUser::getInstance($modifiedByObj['id'], $modifiedByObj['name']);
        $noteIns->setModifiedBy($modifiedBy);
        $noteIns->setCreatedTime($noteDetails['Created_Time'] ?? null);
        $noteIns->setModifiedTime($noteDetails['Modified_Time'] ?? null);
        if (isset($noteDetails['$voice_note'])) {
            $noteIns->setVoiceNote($noteDetails['$voice_note']);
        }
        if (isset($noteDetails['$se_module'])) {
            $noteIns->setParentModule($noteDetails['$se_module']);
        }
        $parentDetails = $noteDetails['Parent_Id'] ?? null;
        if (null != $parentDetails) {
            if (isset($parentDetails['id'])) {
                $noteIns->setParentId($parentDetails['id']);
            }
            if (isset($parentDetails['name'])) {
                $noteIns->setParentName($parentDetails['name']);
            }
        }
        if (isset($noteDetails['$size']) && null != $noteDetails['$size']) {
            $noteIns->setSize($noteDetails['$size']);
        }
        if (isset($noteDetails['$attachments']) && null != $noteDetails['$attachments']) {
            $attachmentsObj = $noteDetails['$attachments'];
            $attachmentInsArr = [];
            foreach ($attachmentsObj as $singleAttachment) {
                array_push($attachmentInsArr, self::getZCRMAttachment($singleAttachment));
            }
            $noteIns->setAttachments($attachmentInsArr);
        }

        return $noteIns;
    }

    private function getZCRMAttachment($attachmentDetails)
    {
        $attachmentIns = ZCRMAttachment::getInstance($this->parentRecord, $attachmentDetails['id']);
        $fileName = $attachmentDetails['File_Name'];
        $attachmentIns->setFileName($fileName);
        $attachmentIns->setFileType(substr($fileName, strrpos($fileName, '.') + 1, strlen($fileName)));
        $attachmentIns->setSize($attachmentDetails['Size']);
        $ownerObj = $attachmentDetails['Owner'];
        $owner = ZCRMUser::getInstance($ownerObj['id'], $ownerObj['name']);
        $attachmentIns->setOwner($owner);
        $createdByObj = $attachmentDetails['Created_By'];
        $createdBy = ZCRMUser::getInstance($createdByObj['id'], $createdByObj['name']);
        $attachmentIns->setCreatedBy($createdBy);
        $modifiedByObj = $attachmentDetails['Modified_By'];
        $modifiedBy = ZCRMUser::getInstance($modifiedByObj['id'], $modifiedByObj['name']);
        $attachmentIns->setModifiedBy($modifiedBy);
        $attachmentIns->setCreatedTime($attachmentDetails['Created_Time']);
        $attachmentIns->setModifiedTime($attachmentDetails['Modified_Time']);
        $attachmentIns->setParentModule($attachmentDetails['$se_module']);
        $attachmentIns->setAttachmentType($attachmentDetails['$type']);
        $parentDetails = $attachmentDetails['Parent_Id'];
        $attachmentIns->setParentId($parentDetails['id']);
        $attachmentIns->setParentName($parentDetails['name']);

        return $attachmentIns;
    }
}
