<?php

namespace zcrmsdk\crm\api\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\crud\ZCRMNote;
use zcrmsdk\crm\crud\ZCRMOrgTax;
use zcrmsdk\crm\crud\ZCRMPermission;
use zcrmsdk\crm\crud\ZCRMProfileCategory;
use zcrmsdk\crm\crud\ZCRMProfileSection;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\setup\users\ZCRMProfile;
use zcrmsdk\crm\setup\users\ZCRMRole;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\setup\users\ZCRMUserCustomizeInfo;
use zcrmsdk\crm\setup\users\ZCRMUserTheme;
use zcrmsdk\crm\utility\APIConstants;

/**
 * Purpose of this class is to fire User level APIs and construct the response.
 *
 * @author sumanth-3058
 */
class OrganizationAPIHandler extends APIHandler
{
    private function __construct()
    {
    }

    public static function getInstance()
    {
        return new OrganizationAPIHandler();
    }

    public function getNotes(array $param_map = [], array $header_map = [])
    {
        try {
            $this->urlPath = 'Notes';
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
            $notesDetails = $responseJSON['data'];
            $noteInstancesArray = [];
            foreach ($notesDetails as $notesObj) {
                $record_Ins = ZCRMRecord::getInstance($notesObj['$se_module'], $notesObj['Parent_Id']['id']);
                $noteIns = ZCRMNote::getInstance($record_Ins, $notesObj['id']);
                $noteInstancesArray[] = RelatedListAPIHandler::getInstance($record_Ins, 'Notes')->getZCRMNote($notesObj, $noteIns);
            }
            $responseInstance->setData($noteInstancesArray);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function createNotes(array $noteInstances): BulkAPIResponse
    {
        if (sizeof($noteInstances) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_NOTES_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $dataArray = [];
            foreach ($noteInstances as $noteInstance) {
                if (null == $noteInstance->getId()) {
                    $record_Ins = ZCRMRecord::getInstance($noteInstance->getParentModule(), $noteInstance->getParentId());
                    $dataArray[] = RelatedListAPIHandler::getInstance($record_Ins, 'Notes')->getZCRMNoteAsJSON($noteInstance);
                } else {
                    throw new ZCRMException(' ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
            }

            $requestBodyObj = [];
            $requestBodyObj['data'] = $dataArray;
            $this->urlPath = 'Notes';
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $requestBodyObj;

            return APIRequest::getInstance($this)->getBulkAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteNotes(array $noteIds): BulkAPIResponse
    {
        if (sizeof($noteIds) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_NOTES_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $this->urlPath = 'Notes';
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->addHeader('Content-Type', 'application/json');
            $this->addParam('ids', implode(',', $noteIds)); // converts array to string with specified seperator

            return APIRequest::getInstance($this)->getBulkAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getOrganizationDetails(): APIResponse
    {
        try {
            $this->urlPath = 'org';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $orgDetails = $responseJSON['org'][0];
            $responseInstance->setData(self::setOrganizationDetails($orgDetails));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getOrganizationTaxes(): BulkAPIResponse
    {
        try {
            $this->urlPath = 'org/taxes';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $orgTaxes = $responseJSON['taxes'];
            $orgTaxInstancesArray = [];
            foreach ($orgTaxes as $orgTaxobj) {
                $orgTaxInstancesArray[] = self::getZCRMorgTax($orgTaxobj);
            }
            $responseInstance->setData($orgTaxInstancesArray);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getOrganizationTax(string $orgTaxId): APIResponse
    {
        try {
            $this->urlPath = 'org/taxes/' . $orgTaxId;
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $orgTax = $responseJSON['taxes'][0];
            $responseInstance->setData(self::getZCRMorgTax($orgTax));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function createOrganizationTaxes(array $orgTaxInstances): BulkAPIResponse
    {
        if (sizeof($orgTaxInstances) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_ORGTAX_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $dataArray = [];
            foreach ($orgTaxInstances as $orgTaxInstance) {
                if (null == $orgTaxInstance->getId()) {
                    $dataArray[] = self::constructJSONForZCRMOrgTax($orgTaxInstance);
                } else {
                    throw new ZCRMException(' ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
            }

            $requestBodyObj = [];
            $requestBodyObj['taxes'] = $dataArray;
            $this->urlPath = 'org/taxes';
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $requestBodyObj;

            return APIRequest::getInstance($this)->getBulkAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function updateOrganizationTaxes(array $orgTaxInstances): BulkAPIResponse
    {
        if (sizeof($orgTaxInstances) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_ORGTAX_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $dataArray = [];
            foreach ($orgTaxInstances as $orgTaxInstance) {
                if (null != $orgTaxInstance->getId()) {
                    $dataArray[] = self::constructJSONForZCRMOrgTax($orgTaxInstance);
                } else {
                    throw new ZCRMException(' ID MUST not be null for update operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
            }
            $requestBodyObj = [];
            $requestBodyObj['taxes'] = $dataArray;
            $this->urlPath = 'org/taxes';
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $requestBodyObj;

            return APIRequest::getInstance($this)->getBulkAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteOrganizationTax(array $orgTaxId): APIResponse
    {
        try {
            $this->urlPath = 'org/taxes/' . $orgTaxId;
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->addHeader('Content-Type', 'application/json');

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteOrganizationTaxes(array $orgTaxIds): BulkAPIResponse
    {
        if (sizeof($orgTaxIds) > 100) {
            throw new ZCRMException(APIConstants::API_MAX_ORGTAX_MSG, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
        try {
            $this->urlPath = 'org/taxes';
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->addHeader('Content-Type', 'application/json');
            $this->addParam('ids', implode(',', $orgTaxIds)); // converts array to string with specified seperator

            return APIRequest::getInstance($this)->getBulkAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getZCRMorgTax(array $orgTaxDetails): ZCRMOrgTax
    {
        $orgTaxInstance = ZCRMOrgTax::getInstance($orgTaxDetails['name'], $orgTaxDetails['id']);
        $orgTaxInstance->setValue($orgTaxDetails['display_label']);
        $orgTaxInstance->setDisplayName($orgTaxDetails['value']);

        return $orgTaxInstance;
    }

    public function setOrganizationDetails(array $orgDetails): ZCRMOrganization
    {
        $orgInsatance = ZCRMOrganization::getInstance($orgDetails['company_name'], $orgDetails['id']);
        $orgInsatance->setAlias($orgDetails['alias']);
        $orgInsatance->setCity($orgDetails['city']);
        $orgInsatance->setCountry($orgDetails['country']);
        $orgInsatance->setCountryCode($orgDetails['country_code']);
        $orgInsatance->setCurrencyLocale($orgDetails['currency_locale']);
        $orgInsatance->setCurrencySymbol($orgDetails['currency_symbol']);
        $orgInsatance->setDescription($orgDetails['description']);
        $orgInsatance->setEmployeeCount($orgDetails['employee_count']);
        $orgInsatance->setFax($orgDetails['fax']);
        $orgInsatance->setGappsEnabled((bool) $orgDetails['gapps_enabled']);
        $orgInsatance->setIsoCode($orgDetails['iso_code']);
        $orgInsatance->setMcStatus($orgDetails['mc_status']);
        $orgInsatance->setMobile($orgDetails['mobile']);

        $orgInsatance->setPhone($orgDetails['phone']);
        $orgInsatance->setPrimaryEmail($orgDetails['primary_email']);
        $orgInsatance->setPrimaryZuid($orgDetails['primary_zuid']);
        $orgInsatance->setState($orgDetails['state']);
        $orgInsatance->setStreet($orgDetails['street']);
        $orgInsatance->setTimeZone($orgDetails['time_zone']);
        $orgInsatance->setWebsite($orgDetails['website']);
        $orgInsatance->setZgid($orgDetails['zgid']);
        $orgInsatance->setZipCode($orgDetails['zip']);

        $license_details = $orgDetails['license_details'];
        if (null != $license_details) {
            $orgInsatance->setPaidAccount((bool) $license_details['paid']);
            $orgInsatance->setPaidType($license_details['paid_type']);
            $orgInsatance->setPaidExpiry($license_details['paid_expiry']);
            $orgInsatance->setTrialExpiry($license_details['trial_expiry']);
            $orgInsatance->setTrialType($license_details['trial_type']);
        }

        return $orgInsatance;
    }

    public function getAllRoles(): BulkAPIResponse
    {
        try {
            $this->urlPath = 'settings/roles';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $roles = $responseJSON['roles'];
            $roleInstanceArray = [];
            foreach ($roles as $role) {
                $roleInstanceArray[] = self::getZCRMRole($role);
            }
            $responseInstance->setData($roleInstanceArray);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getRole(string $roleId): APIResponse
    {
        try {
            $this->urlPath = 'settings/roles/' . $roleId;
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $roles = $responseJSON['roles'];
            $responseInstance->setData(self::getZCRMRole($roles[0]));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getZCRMRole(array $roleDetails): ZCRMRole
    {
        $crmRoleInstance = ZCRMRole::getInstance($roleDetails['id'], $roleDetails['name']);
        $crmRoleInstance->setDisplayLabel($roleDetails['display_label']);
        $crmRoleInstance->setAdminRole((bool) $roleDetails['admin_user']);
        if (isset($roleDetails['reporting_to'])) {
            $crmRoleInstance->setReportingTo(ZCRMRole::getInstance($roleDetails['reporting_to']['id'], $roleDetails['reporting_to']['name']));
        }

        return $crmRoleInstance;
    }

    /**
     * @param $userInstance
     * @return APIResponse
     * @throws ZCRMException
     */
    public function createUser(ZCRMUser $userInstance): APIResponse
    {
        try {
            $userJson = self::constructJSONForUser([
                $userInstance,
            ]);
            $this->urlPath = 'users';
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $userJson;
            $this->apiKey = 'users';

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function updateUser(ZCRMUser $userInstance): APIResponse
    {
        try {
            $userJson = self::constructJSONForUser([
                $userInstance,
            ]);
            $this->urlPath = 'users/' . $userInstance->getId();
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->addHeader('Content-Type', 'application/json');
            $this->requestBody = $userJson;
            $this->apiKey = 'users';

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteUser(string $userId): APIResponse
    {
        try {
            $this->urlPath = 'users/' . $userId;
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->addHeader('Content-Type', 'application/json');
            $this->apiKey = 'users';

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function constructJSONForZCRMOrgTax($orgTaxInstance): array
    {
        $orgTaxJson = [];

        if (null != $orgTaxInstance->getId()) {
            $orgTaxJson['id'] = $orgTaxInstance->getId();
        }
        if (null != $orgTaxInstance->getName()) {
            $orgTaxJson['name'] = $orgTaxInstance->getName();
        }
        if (null != $orgTaxInstance->getDisplayName()) {
            $orgTaxJson['display_label'] = $orgTaxInstance->getDisplayName();
        }
        if (null != $orgTaxInstance->getValue()) {
            $orgTaxJson['value'] = $orgTaxInstance->getValue();
        }

        return $orgTaxJson;
    }

    public function constructJSONForUser($userInstanceArray): bool|string
    {
        $userArray = [];
        foreach ($userInstanceArray as $user) {
            $userInfoJson = [];
            $userRole = $user->getRole();
            if (null != $userRole) {
                $userInfoJson['role'] = $userRole->getId();
            }
            $userProfile = $user->getProfile();
            if (null != $userProfile) {
                $userInfoJson['profile'] = $userProfile->getId();
            }
            if (null != $user->getCountry()) {
                $userInfoJson['country'] = $user->getCountry();
            }
            if (null != $user->getName()) {
                $userInfoJson['name'] = $user->getName();
            }
            if (null != $user->getCity()) {
                $userInfoJson['city'] = $user->getCity();
            }
            if (null != $user->getSignature()) {
                $userInfoJson['signature'] = $user->getSignature();
            }
            if (null != $user->getNameFormat()) {
                $userInfoJson['name_format'] = $user->getNameFormat();
            }
            if (null != $user->getLanguage()) {
                $userInfoJson['language'] = $user->getLanguage();
            }
            if (null != $user->getLocale()) {
                $userInfoJson['locale'] = $user->getLocale();
            }
            if (null != $user->isPersonalAccount()) {
                $userInfoJson['personal_account'] = (bool) $user->isPersonalAccount();
            }
            if (null != $user->getDefaultTabGroup()) {
                $userInfoJson['default_tab_group'] = $user->getDefaultTabGroup();
            }
            if (null != $user->getStreet()) {
                $userInfoJson['street'] = $user->getStreet();
            }
            if (null != $user->getAlias()) {
                $userInfoJson['alias'] = $user->getAlias();
            }
            if (null != $user->getState()) {
                $userInfoJson['state'] = $user->getState();
            }
            if (null != $user->getCountryLocale()) {
                $userInfoJson['country_locale'] = $user->getCountryLocale();
            }
            if (null != $user->getFax()) {
                $userInfoJson['fax'] = $user->getFax();
            }
            if (null != $user->getFirstName()) {
                $userInfoJson['first_name'] = $user->getFirstName();
            }
            if (null != $user->getEmail()) {
                $userInfoJson['email'] = $user->getEmail();
            }
            if (null != $user->getZip()) {
                $userInfoJson['zip'] = $user->getZip();
            }
            if (null != $user->getDecimalSeparator()) {
                $userInfoJson['decimal_separator'] = $user->getDecimalSeparator();
            }
            if (null != $user->getWebsite()) {
                $userInfoJson['website'] = $user->getWebsite();
            }
            if (null != $user->getTimeFormat()) {
                $userInfoJson['time_format'] = $user->getTimeFormat();
            }
            if (null != $user->getMobile()) {
                $userInfoJson['mobile'] = $user->getMobile();
            }
            if (null != $user->getLastName()) {
                $userInfoJson['last_name'] = $user->getLastName();
            }
            if (null != $user->getTimeZone()) {
                $userInfoJson['time_zone'] = $user->getTimeZone();
            }
            if (null != $user->getPhone()) {
                $userInfoJson['phone'] = $user->getPhone();
            }
            if (null != $user->getDob()) {
                $userInfoJson['dob'] = $user->getDob();
            }
            if (null != $user->getDateFormat()) {
                $userInfoJson['date_format'] = $user->getDateFormat();
            }
            if (null != $user->getStatus()) {
                $userInfoJson['status'] = $user->getStatus();
            }
            $customFields = $user->getData();
            foreach ($customFields as $key => $value) {
                $userInfoJson[$key] = $value;
            }
            $userArray[] = $userInfoJson;
        }

        return json_encode([
            'users' => $userArray,
        ]);
    }

    public function getAllProfiles(): BulkAPIResponse
    {
        try {
            $this->urlPath = 'settings/profiles';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $profiles = $responseJSON['profiles'];
            $profileInstanceArray = [];
            foreach ($profiles as $profile) {
                $profileInstanceArray[] = self::getZCRMProfile($profile);
            }
            $responseInstance->setData($profileInstanceArray);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getProfile(string $profileId): APIResponse
    {
        try {
            $this->urlPath = 'settings/profiles/' . $profileId;
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $profiles = $responseJSON['profiles'];
            $responseInstance->setData(self::getZCRMProfile($profiles[0]));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getZCRMProfile(array $profileDetails): ZCRMProfile
    {
        $profileInstance = ZCRMProfile::getInstance($profileDetails['id'], $profileDetails['name']);
        $profileInstance->setCreatedTime($profileDetails['created_time']);
        $profileInstance->setModifiedTime($profileDetails['modified_time']);
        $profileInstance->setDescription($profileDetails['description']);
        $profileInstance->setCategory($profileDetails['category']);
        if (null != $profileDetails['modified_by']) {
            $profileInstance->setModifiedBy(ZCRMUser::getInstance($profileDetails['modified_by']['id'], $profileDetails['modified_by']['name']));
        }
        if (null != $profileDetails['created_by']) {
            $profileInstance->setCreatedBy(ZCRMUser::getInstance($profileDetails['created_by']['id'], $profileDetails['created_by']['name']));
        }
        if (isset($profileDetails['permissions_details'])) {
            $permissions = $profileDetails['permissions_details'];
            foreach ($permissions as $permission) {
                $perIns = ZCRMPermission::getInstance($permission['name'], $permission['id']);
                $perIns->setDisplayLabel($permission['display_label']);
                $perIns->setModule($permission['module']);
                $perIns->setEnabled(boolval($permission['enabled']));
                $profileInstance->addPermission($perIns);
            }
        }
        if (isset($profileDetails['sections'])) {
            $sections = $profileDetails['sections'];
            foreach ($sections as $section) {
                $zcrmProfileSection = ZCRMProfileSection::getInstance($section['name']);
                if (isset($section['categories'])) {
                    $categories = $section['categories'];
                    foreach ($categories as $category) {
                        $categoryIns = ZCRMProfileCategory::getInstance($category['name']);
                        $categoryIns->setDisplayLabel($category['display_label']);
                        $categoryIns->setPermissionIds($category['permissions_details']);
                        if (isset($category['module'])) {
                            $categoryIns->setModule($category['module']);
                        }
                        $zcrmProfileSection->addCategory($categoryIns);
                    }
                }
                $profileInstance->addSection($zcrmProfileSection);
            }
        }

        return $profileInstance;
    }

    /**
     * @throws ZCRMException
     */
    public function getUser(string $userId): APIResponse
    {
        try {
            $this->urlPath = 'users/' . $userId;
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $users = $responseJSON['users'];
            $responseInstance->setData(self::getZCRMUser($users[0]));

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function getUsers(array $param_map, array $header_map, string $type): BulkAPIResponse
    {
        try {
            $this->urlPath = 'users';
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
            if (null != $type) {
                $this->addParam('type', $type);
            }
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $users = $responseJSON['users'];
            $userInstanceArray = [];
            foreach ($users as $user) {
                $userInstanceArray[] = self::getZCRMUser($user);
            }
            $responseInstance->setData($userInstanceArray);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function searchUsersByCriteria($criteria, $param_map): BulkAPIResponse
    {
        try {
            $this->urlPath = 'users/search';
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            foreach ($param_map as $key => $value) {
                if (null != $value) {
                    $this->addParam($key, $value);
                }
            }
            $this->addParam('criteria', $criteria);
            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getBulkAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            $users = $responseJSON['users'];
            $userInstanceArray = [];
            foreach ($users as $user) {
                $userInstanceArray[] = self::getZCRMUser($user);
            }
            $responseInstance->setData($userInstanceArray);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    /**
     * @throws ZCRMException
     */
    public function getAllUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'AllUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllDeactiveUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'DeactiveUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllActiveUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'ActiveUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllConfirmedUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'ConfirmedUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllNotConfirmedUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'NotConfirmedUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllDeletedUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'DeletedUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllActiveConfirmedUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'ActiveConfirmedUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllAdminUsers(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'AdminUsers');
    }

    /**
     * @throws ZCRMException
     */
    public function getAllActiveConfirmedAdmins(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        return self::getUsers($param_map, $header_map, 'ActiveConfirmedAdmins');
    }

    /**
     * @throws ZCRMException
     */
    public function getCurrentUser(): BulkAPIResponse
    {
        return self::getUsers([], [], 'CurrentUser');
    }

    public function getZCRMUser($userDetails): ZCRMUser
    {
        $userInstance = ZCRMUser::getInstance($userDetails['id'], isset($userDetails['name']) ? $userDetails['name'] : null);
        $userInstance->setCountry($userDetails['country'] ?? null);
        $roleInstance = ZCRMRole::getInstance($userDetails['role']['id'], $userDetails['role']['name']);
        $userInstance->setRole($roleInstance);
        if (array_key_exists('customize_info', $userDetails)) {
            $userInstance->setCustomizeInfo(self::getZCRMUserCustomizeInfo($userDetails['customize_info']));
        }
        $userInstance->setCity($userDetails['city']);
        $userInstance->setSignature($userDetails['signature'] ?? null);

        $userInstance->setNameFormat($userDetails['name_format'] ?? null);
        $userInstance->setLanguage($userDetails['language']);
        $userInstance->setLocale($userDetails['locale']);
        $userInstance->setPersonalAccount($userDetails['personal_account'] ?? null);
        $userInstance->setDefaultTabGroup($userDetails['default_tab_group'] ?? null);
        $userInstance->setAlias($userDetails['alias']);
        $userInstance->setStreet($userDetails['street']);
        if (array_key_exists('theme', $userDetails)) {
            $userInstance->setTheme(self::getZCRMUserTheme($userDetails['theme']));
        }
        $userInstance->setState($userDetails['state']);
        $userInstance->setCountryLocale($userDetails['country_locale']);
        $userInstance->setFax($userDetails['fax']);
        $userInstance->setFirstName($userDetails['first_name']);
        $userInstance->setEmail($userDetails['email']);
        $userInstance->setZip($userDetails['zip']);
        $userInstance->setDecimalSeparator($userDetails['decimal_separator'] ?? null);
        $userInstance->setWebsite($userDetails['website']);
        $userInstance->setTimeFormat($userDetails['time_format']);
        $profile = ZCRMProfile::getInstance($userDetails['profile']['id'], $userDetails['profile']['name']);
        $userInstance->setProfile($profile);
        $userInstance->setMobile($userDetails['mobile']);
        $userInstance->setLastName($userDetails['last_name']);
        $userInstance->setTimeZone($userDetails['time_zone']);
        $userInstance->setZuid($userDetails['zuid']);
        $userInstance->setConfirm($userDetails['confirm']);
        $userInstance->setFullName($userDetails['full_name']);
        $userInstance->setPhone($userDetails['phone']);
        $userInstance->setDob($userDetails['dob']);
        $userInstance->setDateFormat($userDetails['date_format']);
        $userInstance->setStatus($userDetails['status']);
        $userInstance->setTerritories($userDetails['territories'] ?? null);
        $userInstance->setReportingTo($userDetails['reporting_to'] ?? null);
        $userInstance->setCreatedBy($userDetails['created_by']);
        $userInstance->setModifiedBy($userDetails['Modified_By']);
        $userInstance->setIsOnline($userDetails['Isonline']);
        $userInstance->setCurrency($userDetails['Currency'] ?? null);
        $userInstance->setCreatedTime($userDetails['created_time']);
        $userInstance->setModifiedTime($userDetails['Modified_Time']);
        foreach ($userDetails as $key => $value) {
            if (!in_array($key, ZCRMUser::$defaultKeys)) {
                $userInstance->setFieldValue($key, $value);
            }
        }

        return $userInstance;
    }

    public function getZCRMUserCustomizeInfo(array $customizeInfo): ZCRMUserCustomizeInfo
    {
        $customizeInfoInstance = ZCRMUserCustomizeInfo::getInstance();
        if (null != $customizeInfo['notes_desc']) {
            $customizeInfoInstance->setNotesDesc($customizeInfo['notes_desc']);
        }
        if (null != $customizeInfo['show_right_panel']) {
            $customizeInfoInstance->setIsToShowRightPanel((bool) $customizeInfo['show_right_panel']);
        }
        if (null != $customizeInfo['bc_view']) {
            $customizeInfoInstance->setBcView($customizeInfo['bc_view']);
        }
        if (null != $customizeInfo['show_home']) {
            $customizeInfoInstance->setIsToShowHome((bool) $customizeInfo['show_home']);
        }
        if (null != $customizeInfo['show_detail_view']) {
            $customizeInfoInstance->setIsToShowDetailView((bool) $customizeInfo['show_detail_view']);
        }
        if (null != $customizeInfo['unpin_recent_item']) {
            $customizeInfoInstance->setUnpinRecentItem($customizeInfo['unpin_recent_item']);
        }

        return $customizeInfoInstance;
    }

    public function getZCRMUserTheme(array $themeDetails): ZCRMUserTheme
    {
        $themeInstance = ZCRMUserTheme::getInstance();
        $themeInstance->setNormalTabFontColor($themeDetails['normal_tab']['font_color']);
        $themeInstance->setNormalTabBackground($themeDetails['normal_tab']['background']);
        $themeInstance->setSelectedTabFontColor($themeDetails['selected_tab']['font_color']);
        $themeInstance->setSelectedTabBackground($themeDetails['selected_tab']['background']);

        return $themeInstance;
    }
}
