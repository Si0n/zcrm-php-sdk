<?php

namespace zcrmsdk\crm\bulkapi\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\handler\APIHandler;
use zcrmsdk\crm\bulkcrud\ZCRMBulkCallBack;
use zcrmsdk\crm\bulkcrud\ZCRMBulkCriteria;
use zcrmsdk\crm\bulkcrud\ZCRMBulkQuery;
use zcrmsdk\crm\bulkcrud\ZCRMBulkResult;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;

class BulkReadAPIHandler extends APIHandler
{
    protected $record;
    private $index = 1;

    private function __construct($zcrmbulkread)
    {
        $this->record = $zcrmbulkread;
    }

    public static function getInstance($zcrmbulkread)
    {
        return new BulkReadAPIHandler($zcrmbulkread);
    }

    public function getBulkReadJobDetails()
    {
        try {
            if (null == $this->record->getJobId()) {
                throw new ZCRMException('JOB ID must not be null for get operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = APIConstants::READ . '/' . $this->record->getJobId();
            $this->addHeader('Content-Type', 'application/json');
            $this->isBulk = true;

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            $recordDetails = $responseInstance->getResponseJSON()[APIConstants::DATA];
            self::setBulkReadRecordProperties($recordDetails[0]);
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function createBulkReadJob()
    {
        try {
            if (null != $this->record->getJobId()) {
                throw new ZCRMException('JOB ID must be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = APIConstants::READ;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = json_encode(self::getZCRMBulkQueryAsJSON());
            $this->isBulk = true;
            // fire request
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            $responseDataArray = $responseInstance->getResponseJSON()[APIConstants::DATA];
            $responseData = $responseDataArray[0];
            $reponseDetails = $responseData[APIConstants::DETAILS];
            self::setBulkReadRecordProperties($reponseDetails);
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function downloadBulkReadResult()
    {
        try {
            if (null == $this->record->getJobId()) {
                throw new ZCRMException('JOB ID must not be null for get bulk read result operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = APIConstants::READ . '/' . $this->record->getJobId() . '/' . APIConstants::RESULT;
            $this->isBulk = true;

            return APIRequest::getInstance($this)->downloadFile();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    private function setBulkReadRecordProperties($recordDetails)
    {
        foreach ($recordDetails as $key => $value) {
            if ('id' == $key && null != $value) {
                $this->record->setJobId($value);
            } elseif ('operation' == $key && null != $value) {
                $this->record->setOperation($value);
            } elseif ('state' == $key && null != $value) {
                $this->record->setState($value);
            } elseif ('created_by' == $key && null != $value) {
                $createdBy = ZCRMUser::getInstance($value['id'], $value['name']);
                $this->record->setCreatedBy($createdBy);
            } elseif ('created_time' == $key && null != $value) {
                $this->record->setCreatedTime($value);
            } elseif ('result' == $key && null != $value) {
                $this->record->setResult(self::setZCRMResultObject($value));
            } elseif ('query' == $key && null != $value) {
                $this->record->setQuery(self::setZCRMBulkQueryObject($value));
            } elseif ('callback' == $key && null != $value) {
                $this->record->setCallback(self::setZCRMBulkReadCallbackObject($value));
            } elseif ('file_type' == $key && null != $value) {
                $this->record->setFileType($value);
            }
        }
    }

    private function setZCRMBulkQueryObject($queryValue)
    {
        $query = ZCRMBulkQuery::getInstance();
        foreach ($queryValue as $key => $value) {
            if ('module' == $key && null != $value) {
                $this->record->setModuleAPIName($value);
                $query->setModuleAPIName($value);
            } elseif ('page' == $key && null != $value) {
                $query->setPage($value);
            } elseif ('cvid' == $key && null != $value) {
                $query->setCvId($value);
            } elseif ('fields' == $key && null != $value) {
                $query->setFields($value);
            } elseif ('criteria' == $key && null != $value) {
                $this->index = 1;
                $criteriaInstance = self::setZCRMBulkCriteriaObject($value, $this->index);
                $query->setCriteria($criteriaInstance);
                $query->setCriteriaPattern($criteriaInstance->getPattern());
                $query->setCriteriaCondition($criteriaInstance->getCriteria());
            }
        }

        return $query;
    }

    private function setZCRMBulkCriteriaObject($criteriaJSON)
    {
        $recordCriteria = ZCRMBulkCriteria::getInstance();
        $recordCriteria->setAPIName(isset($criteriaJSON['api_name']) ? $criteriaJSON['api_name'] : null);
        $recordCriteria->setComparator(isset($criteriaJSON['comparator']) ? $criteriaJSON['comparator'] : null);
        if (isset($criteriaJSON['value'])) {
            if (is_bool($criteriaJSON['value'])) {
                $recordCriteria->setValue((bool) $criteriaJSON['value']);
            } else {
                $recordCriteria->setValue($criteriaJSON['value']);
            }
            $recordCriteria->setIndex($this->index);
            $recordCriteria->setPattern((string) $this->index);
            ++$this->index;
            $recordCriteria->setCriteria('(' . $criteriaJSON['api_name'] . ':' . $criteriaJSON['comparator'] . ':' . json_encode($recordCriteria->getValue()) . ')');
        }

        if (isset($criteriaJSON['group'])) {
            $group_criteria = [];
            foreach ($criteriaJSON['group'] as $group) {
                array_push($group_criteria, self::setZCRMBulkCriteriaObject($group));
            }
            $recordCriteria->setGroup($group_criteria);
        }

        if (isset($criteriaJSON['group_operator'])) {
            $criteriavalue = '(';
            $pattern = '(';
            $recordCriteria->setGroupOperator($criteriaJSON['group_operator']);
            $count = sizeof($group_criteria);
            $i = 0;
            foreach ($group_criteria as $criteriaObj) {
                ++$i;
                $criteriavalue .= $criteriaObj->getCriteria();
                $pattern .= $criteriaObj->getPattern();
                if ($i < $count) {
                    $criteriavalue .= $recordCriteria->getGroupOperator();
                    $pattern .= $recordCriteria->getGroupOperator();
                }
            }
            $recordCriteria->setCriteria($criteriavalue . ')');
            $recordCriteria->setPattern($pattern . ')');

            // $recordCriteria->setCriteria("(".$group_criteria[0]->getCriteria().$recordCriteria->getGroupOperator().$group_criteria[1]->getCriteria().")");
            // $recordCriteria->setPattern("(".$group_criteria[0]->getPattern().$recordCriteria->getGroupOperator().$group_criteria[1]->getPattern().")");
        }

        return $recordCriteria;
    }

    private function getZCRMBulkQueryAsJSON()
    {
        $requestBodyObject = [];
        $recordJSON = [];
        if (null != $this->record->getModuleAPIName()) {
            $recordJSON['module'] = $this->record->getModuleAPIName();
        }
        if (null != $this->record->getQuery()) {
            $query = $this->record->getQuery();
            if (null != $query->getFields() && sizeof($query->getFields()) > 0) {
                $recordJSON['fields'] = $query->getFields();
            }
            if ($query->getPage() > 0) {
                $recordJSON['page'] = $query->getPage();
            }
            if (null != $query->getCriteria()) {
                $recordJSON['criteria'] = self::getCriteriaAsJSONObject($query->getCriteria());
            }
            if (0 != $query->getCvId()) {
                $recordJSON['cvid'] = $query->getCvId();
            }
        }
        if (null != $this->record->getCallback()) {
            $requestBodyObject[APIConstants::CALLBACK] = self::getCallBackAsJSONObject($this->record->getCallback());
        }
        if (null != $this->record->getFileType()) {
            $requestBodyObject[APIConstants::FILETYPE] = $this->record->getFileType();
        }
        $requestBodyObject[APIConstants::QUERY] = $recordJSON;

        return $requestBodyObject;
    }

    private function getCriteriaAsJSONObject(ZCRMBulkCriteria $criteria)
    {
        $recordCriteria = [];
        if (null != $criteria->getAPIName()) {
            $recordCriteria['api_name'] = $criteria->getAPIName();
        }
        if (null != $criteria->getValue() || is_bool($criteria->getValue())) {
            $recordCriteria['value'] = $criteria->getValue();
        }
        if (null != $criteria->getGroupOperator()) {
            $recordCriteria['group_operator'] = $criteria->getGroupOperator();
        }
        if (null != $criteria->getComparator()) {
            $recordCriteria['comparator'] = $criteria->getComparator();
        }
        if (null != $criteria->getGroup() && sizeof($criteria->getGroup()) > 0) {
            $recordData = [];
            foreach ($criteria->getGroup() as $group) {
                array_push($recordData, self::getCriteriaAsJSONObject($group));
            }
            $recordCriteria['group'] = $recordData;
        }

        return $recordCriteria;
    }

    private function getCallBackAsJSONObject(ZCRMBulkCallBack $callback)
    {
        $callbackJSON = [];
        if (null != $callback->getUrl()) {
            $callbackJSON['url'] = $callback->getUrl();
        }
        if (null != $callback->getMethod()) {
            $callbackJSON['method'] = $callback->getMethod();
        }

        return $callbackJSON;
    }

    private function setZCRMBulkReadCallbackObject($callbackJSON)
    {
        $callback = ZCRMBulkCallBack::getInstance();
        if (array_key_exists('url', $callbackJSON) && null != $callbackJSON['url']) {
            $callback->setUrl($callbackJSON['url']);
        }
        if (array_key_exists('method', $callbackJSON) && null != $callbackJSON['method']) {
            $callback->setMethod($callbackJSON['method']);
        }

        return $callback;
    }

    private function setZCRMResultObject($resultJSON)
    {
        $result = ZCRMBulkResult::getInstance();
        if (array_key_exists('download_url', $resultJSON) && null != $resultJSON['download_url']) {
            $result->setDownloadUrl($resultJSON['download_url']);
        }
        if (array_key_exists('page', $resultJSON)) {
            $result->setPage($resultJSON['page'] + 0);
        }
        if (array_key_exists('count', $resultJSON)) {
            $result->setCount($resultJSON['count'] + 0);
        }
        if (array_key_exists('per_page', $resultJSON)) {
            $result->setPerPage($resultJSON['per_page'] + 0);
        }
        if (array_key_exists('more_records', $resultJSON)) {
            $result->setMoreRecords((bool) $resultJSON['more_records']);
        }

        return $result;
    }
}
