<?php

namespace zcrmsdk\crm\setup\restclient;

use zcrmsdk\crm\api\handler\MetaDataAPIHandler;
use zcrmsdk\crm\api\handler\OrganizationAPIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\bulkcrud\ZCRMBulkRead;
use zcrmsdk\crm\bulkcrud\ZCRMBulkWrite;
use zcrmsdk\crm\crud\ZCRMCustomView;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\utility\ZCRMConfigUtil;
use zcrmsdk\oauth\exception\ZohoOAuthException;

class ZCRMRestClient
{
    private static null|string $CurrentUserEmailID = null;

    private function __construct()
    {
    }

    /**
     * method to get the instance of the rest client.
     *
     * @return ZCRMRestClient instance of the ZCRMRestClient class
     */
    public static function getInstance(): ZCRMRestClient
    {
        return new ZCRMRestClient();
    }

    public static function setCurrentUserEmailId(null|string $UserEmailId): void
    {
        self::$CurrentUserEmailID = $UserEmailId;
    }

    /**
     * method to initialize the rest client.
     *
     * @param array $configuration configuration array containing the configuration details
     *
     * @throws ZohoOAuthException
     */
    public static function initialize(array $configuration): void
    {
        ZCRMConfigUtil::initialize($configuration);
    }

    /**
     * method to get all the modules of the restclient.
     *
     * @return BulkAPIResponse instance of the BulkAPIResponse class containing the bulk api response
     *
     * @throws ZCRMException
     */
    public function getAllModules(): BulkAPIResponse
    {
        return MetaDataAPIHandler::getInstance()->getAllModules();
    }

    /**
     * method to get the module of the rest client.
     *
     * @param string $moduleName api name of the module
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException
     */
    public function getModule(null|string $moduleName): APIResponse
    {
        return MetaDataAPIHandler::getInstance()->getModule($moduleName);
    }

    /**
     * method to get the organization of the rest client.
     *
     * @return ZCRMOrganization instance of the ZCRMOrganization class
     */
    public function getOrganizationInstance(): ZCRMOrganization
    {
        return ZCRMOrganization::getInstance();
    }

    /**
     * method to get the Custom view of the organisation.
     *
     * @return ZCRMCustomView instance of the ZCRMCustomView class
     */
    public function getCustomViewInstance($moduleAPIName, $id): ZCRMCustomView
    {
        return ZCRMCustomView::getInstance($moduleAPIName, $id);
    }

    /**
     * method to get the module of the rest client.
     *
     * @param string $moduleAPIName module api name
     *
     * @return ZCRMModule instance of the ZCRMModule class
     */
    public function getModuleInstance(string $moduleAPIName): ZCRMModule
    {
        return ZCRMModule::getInstance($moduleAPIName);
    }

    /**
     * method to get the record of the client.
     *
     * @param string $moduleAPIName module api name
     * @param string $entityId      record id
     *
     * @return ZCRMRecord instance of the ZCRMRecord class
     */
    public function getRecordInstance(null|string $moduleAPIName, null|string $entityId): ZCRMRecord
    {
        return ZCRMRecord::getInstance($moduleAPIName, $entityId);
    }

    /**
     * method to get the current user of the rest client.
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException
     */
    public function getCurrentUser(): APIResponse|BulkAPIResponse
    {
        return OrganizationAPIHandler::getInstance()->getCurrentUser();
    }

    /**
     * method to get the current user email id.
     *
     * @return string currrent user email id
     */
    public static function getCurrentUserEmailID(): ?string
    {
        return self::$CurrentUserEmailID;
    }

    /**
     * method to get the organization details of the rest client.
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException
     */
    public static function getOrganizationDetails(): APIResponse
    {
        return OrganizationAPIHandler::getInstance()->getOrganizationDetails();
    }

    /**
     * Method to get the bulk read instance.
     *
     * @param string $jobId
     *
     * @return ZCRMBulkRead - class instance
     */
    public function getBulkReadInstance(null|string $moduleName = null, null|string $jobId = null): ZCRMBulkRead
    {
        return ZCRMBulkRead::getInstance($moduleName, $jobId);
    }

    /**
     * Method to get the bulk write instance.
     *
     * @param string $operation     - bulk write operation (insert or update)
     * @param string $jobId         - bulk write job id
     * @param string $moduleAPIName - bulk write module api name
     *
     * @return ZCRMBulkWrite - class instance
     */
    public function getBulkWriteInstance(null|string $operation = null, null|string $jobId = null, null|string $moduleAPIName = null): ZCRMBulkWrite
    {
        return ZCRMBulkWrite::getInstance($operation, $jobId, $moduleAPIName);
    }
}
