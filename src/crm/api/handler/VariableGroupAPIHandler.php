<?php

namespace zcrmsdk\crm\api\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\crud\ZCRMVariableGroup;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;

class VariableGroupAPIHandler extends APIHandler
{
    private $variable_groups = null;

    public static function getInstance()
    {
        return new VariableGroupAPIHandler();
    }

    public function getVariableGroups()
    {
        try {
            $this->urlPath = 'settings/variable_groups';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $this->apiKey = 'variable_groups';
            $response = APIRequest::getInstance($this)->getBulkApiResponse();
            $responseJson = $response->getResponseJson();
            $data = $responseJson['variable_groups'];
            $dataList = [];
            foreach ($data as $jsonData) {
                $variableGroupsIns = ZCRMVariableGroup::getInstance();
                self::getVariableGroupsResAsObj($jsonData, $variableGroupsIns);
                array_push($dataList, $variableGroupsIns);
            }
            $response->setData($dataList);

            return $response;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    private function getVariableGroupsResAsObj($jsonData, $entityInstance)
    {
        foreach ($jsonData as $key => $value) {
            if ('id' == $key) {
                $entityInstance->setId($value);
            } elseif ('name' == $key) {
                $entityInstance->setName($value);
            } elseif ('api_name' == $key) {
                $entityInstance->setApiName($value);
            } elseif ('display_label' == $key) {
                $entityInstance->setDisplayLabel($value);
            } elseif ('description' == $key) {
                $entityInstance->setDescription($value);
            }
        }
    }

    public function getVariableGroup()
    {
        try {
            $this->urlPath = 'settings/variable_groups/'.$this->variable_groups->getId();
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $this->apiKey = 'variable_groups';
            $response = APIRequest::getInstance($this)->getApiResponse();
            $responseJson = $response->getResponseJson();
            $data = $responseJson['variable_groups'];
            $jsonData = $data[0];
            $variableGroupsIns = ZCRMVariableGroup::getInstance();
            self::getVariableGroupResAsObj($jsonData, $variableGroupsIns);
            $response->setData($variableGroupsIns);

            return $response;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    private function getVariableGroupResAsObj($jsonData, $entityInstance)
    {
        foreach ($jsonData as $key => $value) {
            if ('id' == $key) {
                $entityInstance->setId($value);
            } elseif ('name' == $key) {
                $entityInstance->setName($value);
            } elseif ('api_name' == $key) {
                $entityInstance->setApiName($value);
            } elseif ('display_label' == $key) {
                $entityInstance->setDisplayLabel($value);
            } elseif ('description' == $key) {
                $entityInstance->setDescription($value);
            }
        }
    }

    public function setVariableGroups($variable_groups)
    {
        $this->variable_groups = $variable_groups;
    }
}
