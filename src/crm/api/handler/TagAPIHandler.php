<?php

namespace zcrmsdk\crm\api\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\crud\ZCRMTag;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;

class TagAPIHandler extends APIHandler
{
    private function __construct(protected null|ZCRMModule $module = null)
    {
    }

    public static function getInstance(null|ZCRMModule $module = null): TagAPIHandler
    {
        return new TagAPIHandler($module);
    }

    /**
     * @throws ZCRMException
     */
    public function getTags(): BulkAPIResponse
    {
        if (!$this->module) {
            throw new ZCRMException('Module Instance MUST be set to get the tags.', APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $this->urlPath = 'settings/tags';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $this->addParam('module', $this->module->getAPIName());

            // Fire Request
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();

            $responseJSON = $responseInstance->getResponseJSON();
            $tags = $responseJSON[APIConstants::TAGS];
            $tagsList = [];
            foreach ($tags as $tag) {
                $tagInstance = ZCRMTag::getInstance($tag['id']);
                $this->setTagProperties($tagInstance, $tag);
                $tagsList[] = $tagInstance;
            }
            $responseInstance->setData($tagsList);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function getTagCount(string $tagId): APIResponse
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = 'settings/tags/' . $tagId . '/actions/records_count';
            $this->addParam('module', $this->module->getAPIName());

            // Fire Request
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            $tagDetails = $responseInstance->getResponseJSON();
            $tagInstance = ZCRMTag::getInstance($tagId);
            $this->setTagProperties($tagInstance, $tagDetails);
            $responseInstance->setData($tagInstance);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function createTags(null|array $tags): BulkAPIResponse
    {
        if (!$tags) {
            throw new ZCRMException('Tags List MUST be set to create the tags.', APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        if (sizeof($tags) > 50) {
            throw new ZCRMException(APIConstants::API_MAX_TAGS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        if (!$this->module) {
            throw new ZCRMException('Module Instance MUST be set to create the tags.', APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $this->urlPath = 'settings/tags';
            $this->addParam('module', $this->module->getAPIName());
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->addHeader('Content-Type', 'application/json');
            $requestBodyObj = [];
            $dataArray = [];
            foreach ($tags as $tag) {
                if (null != $tag->getId()) {
                    throw new ZCRMException('Tag ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
                $dataArray[] = $this->getZCRMTagAsJSON($tag);
            }
            $requestBodyObj[APIConstants::TAGS] = $dataArray;
            $this->requestBody = $requestBodyObj;

            // Fire Request
            $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();

            $createdTags = [];
            $responses = $bulkAPIResponse->getEntityResponses();
            $size = sizeof($responses);
            for ($i = 0; $i < $size; ++$i) {
                $entityResIns = $responses[$i];
                if (APIConstants::STATUS_SUCCESS === $entityResIns->getStatus()) {
                    $responseData = $entityResIns->getResponseJSON();
                    $tagDetails = $responseData[APIConstants::DETAILS];
                    $newTag = $tags[$i];
                    self::setTagProperties($newTag, $tagDetails);
                    $createdTags[] = $newTag;
                    $entityResIns->setData($newTag);
                } else {
                    $entityResIns->setData(null);
                }
            }
            $bulkAPIResponse->setData($createdTags);

            return $bulkAPIResponse;
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    public function updateTags(array $tags): BulkAPIResponse
    {
        if (sizeof($tags) > 50) {
            throw new ZCRMException(APIConstants::API_MAX_TAGS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        if (!$this->module) {
            throw new ZCRMException('Module Instance MUST be set to update the tags.', APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $this->urlPath = 'settings/tags';
            $this->addParam('module', $this->module->getAPIName());
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->addHeader('Content-Type', 'application/json');
            $requestBodyObj = [];
            $dataArray = [];
            foreach ($tags as $tag) {
                $dataArray[] = self::getZCRMTagAsJSON($tag);
            }
            $requestBodyObj[APIConstants::TAGS] = $dataArray;
            $this->requestBody = $requestBodyObj;

            // Fire Request
            $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();

            $updatedTags = [];
            $responses = $bulkAPIResponse->getEntityResponses();
            $size = sizeof($responses);
            for ($i = 0; $i < $size; ++$i) {
                $entityResIns = $responses[$i];
                if (APIConstants::STATUS_SUCCESS === $entityResIns->getStatus()) {
                    $responseData = $entityResIns->getResponseJSON();
                    $tagDetails = $responseData[APIConstants::DETAILS];
                    $updateTag = $tags[$i];
                    self::setTagProperties($updateTag, $tagDetails);
                    $updatedTags[] = $updateTag;
                    $entityResIns->setData($updateTag);
                } else {
                    $entityResIns->setData(null);
                }
            }
            $bulkAPIResponse->setData($updatedTags);

            return $bulkAPIResponse;
        } catch (ZCRMException $e) {
            throw $e;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function delete(string $tagId): APIResponse
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->urlPath = 'settings/tags/' . $tagId;
            $this->addHeader('Content-Type', 'application/json');

            // Fire Request
            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function merge(string $tagId, string $mergeId): APIResponse
    {
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->urlPath = 'settings/tags/' . $mergeId . '/actions/merge';
        $this->addHeader('Content-Type', 'application/json');
        $tagJSON = [];
        $tagJSON['conflict_id'] = $tagId;
        array_filter($tagJSON);
        $this->requestBody = json_encode(array_filter([
            'tags' => [
                $tagJSON,
            ],
        ]));
        // Fire Request
        $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
        $responseDataArray = $responseInstance->getResponseJSON()[APIConstants::TAGS];
        $responseData = $responseDataArray[0];
        $responseDetails = $responseData[APIConstants::DETAILS];
        $tag = ZCRMTag::getInstance($responseDetails['id']);
        $this->setTagProperties($tag, $responseDetails);
        $responseInstance->setData($tag);

        return $responseInstance;
    }

    /**
     * @throws ZCRMException
     */
    public function update(ZCRMTag $tag): APIResponse
    {
        $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
        $this->urlPath = 'settings/tags/' . $tag->getId();
        $this->addParam('module', $tag->getModuleAPIName());
        $this->addHeader('Content-Type', 'application/json');
        $tagJSON = [];
        $tagJSON['name'] = '' . $tag->getName();
        array_filter($tagJSON);
        $this->requestBody = json_encode(array_filter([
            'tags' => [
                $tagJSON,
            ],
        ]));

        // Fire Request
        $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

        $responseDataArray = $responseInstance->getResponseJSON()[APIConstants::TAGS];
        $responseData = $responseDataArray[0];
        $responseDetails = $responseData[APIConstants::DETAILS];
        $this->setTagProperties($tag, $responseDetails);
        $responseInstance->setData($tag);

        return $responseInstance;
    }

    /**
     * @throws ZCRMException
     */
    public function addTags(ZCRMRecord $record, array $tagNames): APIResponse
    {
        if (sizeof($tagNames) > 10) {
            throw new ZCRMException(APIConstants::API_MAX_RECORD_TAGS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->urlPath = $record->getModuleApiName() . '/' . $record->getEntityId() . '/actions/add_tags';
        $this->addParam('tag_names', implode(',', $tagNames));

        // Fire Request
        $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

        $responseDataArray = $responseInstance->getResponseJSON()[APIConstants::DATA];
        $responseData = $responseDataArray[0];
        $responseDetails = $responseData[APIConstants::DETAILS];
        $addRecordIns = $record;
        EntityAPIHandler::getInstance($addRecordIns)->setRecordProperties($responseDetails);
        $responseInstance->setData($addRecordIns);

        return $responseInstance;
    }

    /**
     * @throws ZCRMException
     */
    public function removeTags(ZCRMRecord $record, array $tagNames): APIResponse
    {
        if (sizeof($tagNames) > 10) {
            throw new ZCRMException(APIConstants::API_MAX_RECORD_TAGS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->urlPath = $record->getModuleApiName() . '/' . $record->getEntityId() . '/actions/remove_tags';
        $this->addParam('tag_names', implode(',', $tagNames));

        // Fire Request
        $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

        $responseDataArray = $responseInstance->getResponseJSON()[APIConstants::DATA];
        $responseData = $responseDataArray[0];
        $responseDetails = $responseData[APIConstants::DETAILS];
        $removeRecordIns = $record;
        EntityAPIHandler::getInstance($removeRecordIns)->setRecordProperties($responseDetails);
        $responseInstance->setData($removeRecordIns);

        return $responseInstance;
    }

    /**
     * @throws ZCRMException
     */
    public function addTagsToRecords(array $recordId, array $tagNames): BulkAPIResponse
    {
        if (sizeof($tagNames) > 10) {
            throw new ZCRMException(APIConstants::API_MAX_RECORD_TAGS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        if (sizeof($recordId) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->urlPath = $this->module->getAPIName() . '/actions/add_tags';
        $this->addParam('ids', implode(',', $recordId));
        $this->addParam('tag_names', implode(',', $tagNames));

        // Fire Request
        $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();

        $recordList = [];
        $responses = $bulkAPIResponse->getEntityResponses();
        foreach ($responses as $entityResIns) {
            if (APIConstants::STATUS_SUCCESS === $entityResIns->getStatus()) {
                $responseData = $entityResIns->getResponseJSON();
                $recordDetails = $responseData[APIConstants::DETAILS];
                $addRecordIns = ZCRMRecord::getInstance($this->module->getAPIName(), $recordDetails['id']);
                EntityAPIHandler::getInstance($addRecordIns)->setRecordProperties($recordDetails);
                $recordList[] = $addRecordIns;
                $entityResIns->setData($addRecordIns);
            } else {
                $entityResIns->setData(null);
            }
        }
        $bulkAPIResponse->setData($recordList);

        return $bulkAPIResponse;
    }

    /**
     * @throws ZCRMException
     */
    public function removeTagsFromRecords(array $recordId, array $tagNames): BulkAPIResponse
    {
        if (sizeof($tagNames) > 10) {
            throw new ZCRMException(APIConstants::API_MAX_RECORD_TAGS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        if (sizeof($recordId) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_RECORDS_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
        $this->urlPath = $this->module->getAPIName() . '/actions/remove_tags';
        $this->addParam('ids', implode(',', $recordId));
        $this->addParam('tag_names', implode(',', $tagNames));

        // Fire Request
        $bulkAPIResponse = APIRequest::getInstance($this)->getBulkAPIResponse();

        $recordList = [];
        $responses = $bulkAPIResponse->getEntityResponses();
        foreach ($responses as $entityResIns) {
            if (APIConstants::STATUS_SUCCESS === $entityResIns->getStatus()) {
                $responseData = $entityResIns->getResponseJSON();
                $recordDetails = $responseData[APIConstants::DETAILS];
                $removeRecordIns = ZCRMRecord::getInstance($this->module->getAPIName(), $recordDetails['id']);
                EntityAPIHandler::getInstance($removeRecordIns)->setRecordProperties($recordDetails);
                $recordList[] = $removeRecordIns;
                $entityResIns->setData($removeRecordIns);
            } else {
                $entityResIns->setData(null);
            }
        }
        $bulkAPIResponse->setData($recordList);

        return $bulkAPIResponse;
    }

    public function setTagProperties(ZCRMTag $tagInstance, array $tagDetails): void
    {
        foreach ($tagDetails as $key => $value) {
            if ('id' == $key) {
                $tagInstance->setId($value);
            } elseif ('name' == $key) {
                $tagInstance->setName($value);
            } elseif ('created_by' == $key) {
                $createdBy = ZCRMUser::getInstance($value['id'], $value['name']);
                $tagInstance->setCreatedBy($createdBy);
            } elseif ('modified_by' == $key) {
                $modifiedBy = ZCRMUser::getInstance($value['id'], $value['name']);
                $tagInstance->setModifiedBy($modifiedBy);
            } elseif ('created_time' == $key) {
                $tagInstance->setCreatedTime('' . $value);
            } elseif ('modified_time' == $key) {
                $tagInstance->setModifiedTime('' . $value);
            } elseif ('count' == $key) {
                $tagInstance->setCount($value);
            }
        }
    }

    public function getZCRMTagAsJSON(ZCRMTag $tag): array
    {
        $recordJSON = [];
        if (null != $tag->getName()) {
            $recordJSON['name'] = '' . $tag->getName();
        }
        if (null != $tag->getId()) {
            $recordJSON['id'] = '' . $tag->getId();
        }

        return array_filter($recordJSON);
    }
}
