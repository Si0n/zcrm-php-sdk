<?php

namespace zcrmsdk\crm\api\handler;

use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\crud\ZCRMEventParticipant;
use zcrmsdk\crm\crud\ZCRMInventoryLineItem;
use zcrmsdk\crm\crud\ZCRMLayout;
use zcrmsdk\crm\crud\ZCRMPriceBookPricing;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\crud\ZCRMTag;
use zcrmsdk\crm\crud\ZCRMTax;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;

class EntityAPIHandler extends APIHandler
{
    protected $record;

    private function __construct($zcrmrecord)
    {
        $this->record = $zcrmrecord;
    }

    public static function getInstance($zcrmrecord)
    {
        return new EntityAPIHandler($zcrmrecord);
    }

    public function getRecord($param_map = [], $header_map = [])
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId();
            foreach ($param_map as $key => $value) {
                if (null !== $value) {
                    $this->addParam($key, $value);
                }
            }
            foreach ($header_map as $key => $value) {
                if (null !== $value) {
                    $this->addHeader($key, $value);
                }
            }

            $this->addHeader('Content-Type', 'application/json');
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $recordDetails = $responseInstance->getResponseJSON()['data'];
            self::setRecordProperties($recordDetails[0]);
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function createRecord($trigger, $lar_id, $process)
    {
        try {
            if (null != $this->record->getEntityId()) {
                throw new ZCRMException('Entity ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = $this->record->getModuleApiName();
            $this->addHeader('Content-Type', 'application/json');
            $requestBodyObj = [];
            $dataArray = [];
            array_push($dataArray, self::getZCRMRecordAsJSON());
            $requestBodyObj['data'] = $dataArray;
            if (null !== $trigger && is_array($trigger)) {
                $requestBodyObj['trigger'] = $trigger;
            }
            if (null !== $lar_id) {
                $requestBodyObj['lar_id'] = $lar_id;
            }
            if (null !== $process && is_array($process)) {
                $requestBodyObj['process'] = $process;
            }

            $this->requestBody = json_encode($requestBodyObj);

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseDataArray = $responseInstance->getResponseJSON()['data'];
            $responseData = $responseDataArray[0];
            $reponseDetails = $responseData['details'];
            $this->record->setEntityId($reponseDetails['id']);
            $this->record->setCreatedTime($reponseDetails['Created_Time']);
            $createdBy = $reponseDetails['Created_By'];
            $this->record->setCreatedBy(ZCRMUser::getInstance($createdBy['id'], $createdBy['name']));
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function updateRecord($trigger, $process)
    {
        try {
            if (null == $this->record->getEntityId()) {
                throw new ZCRMException('Entity ID MUST not be null for update operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_PUT;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId();
            $this->addHeader('Content-Type', 'application/json');
            $requestBodyObj = [];
            $dataArray = [];
            array_push($dataArray, self::getZCRMRecordAsJSON());
            $requestBodyObj['data'] = $dataArray;
            if (null !== $trigger && is_array($trigger)) {
                $requestBodyObj['trigger'] = $trigger;
            }
            if (null !== $process && is_array($process)) {
                $requestBodyObj['process'] = $process;
            }

            $this->requestBody = json_encode($requestBodyObj);
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            $responseDataArray = $responseInstance->getResponseJSON()['data'];
            $responseData = $responseDataArray[0];
            $reponseDetails = $responseData['details'];
            $this->record->setCreatedTime($reponseDetails['Created_Time']);
            $this->record->setModifiedTime($reponseDetails['Modified_Time']);
            $createdBy = $reponseDetails['Created_By'];
            $this->record->setCreatedBy(ZCRMUser::getInstance($createdBy['id'], $createdBy['name']));
            $modifiedBy = $reponseDetails['Modified_By'];
            $this->record->setModifiedBy(ZCRMUser::getInstance($modifiedBy['id'], $modifiedBy['name']));
            $responseInstance->setData($this->record);

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deleteRecord()
    {
        try {
            if (null == $this->record->getEntityId()) {
                throw new ZCRMException('Entity ID MUST not be null for delete operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId();
            $this->addHeader('Content-Type', 'application/json');

            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function convertRecord($potentialRecord, $details)
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId() . '/actions/convert';
            $this->addHeader('Content-Type', 'application/json');
            $dataObject = [];
            if (null != $details) {
                foreach ($details as $key => $value) {
                    if ('overwrite' == $key) {
                        $dataObject['overwrite'] = $value;
                    }
                    if ('notify_lead_owner' == $key) {
                        $dataObject['notify_lead_owner'] = $value;
                    }
                    if ('notify_new_entity_owner' == $key) {
                        $dataObject['notify_new_entity_owner'] = $value;
                    }
                    if ('Accounts' == $key) {
                        $dataObject['Accounts'] = $value;
                    }
                    if ('Contacts' == $key) {
                        $dataObject['Contacts'] = $value;
                    }
                    if ('assign_to' == $key) {
                        $dataObject['assign_to'] = $value;
                    }
                }
            }
            if (null != $potentialRecord) {
                $dataObject['Deals'] = self::getInstance($potentialRecord)->getZCRMRecordAsJSON();
            }
            if (sizeof($dataObject) > 0) {
                $dataArray = json_encode([
                    APIConstants::DATA => [
                        array_filter($dataObject),
                    ],
                ]);
            } else {
                $dataArray = json_encode([
                    APIConstants::DATA => [
                        new \ArrayObject(),
                    ],
                ]);
            }
            $this->requestBody = $dataArray;
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();
            $responseJSON = $responseInstance->getResponseJSON();
            // Process Response JSON
            $convertedIdsJSON = $responseJSON[APIConstants::DATA][0];
            $convertedIds = [];
            $convertedIds[APIConstants::CONTACTS] = isset($convertedIdsJSON[APIConstants::CONTACTS]) ? $convertedIdsJSON[APIConstants::CONTACTS] : null;
            if (isset($convertedIdsJSON[APIConstants::ACCOUNTS]) && null != $convertedIdsJSON[APIConstants::ACCOUNTS]) {
                $convertedIds[APIConstants::ACCOUNTS] = $convertedIdsJSON[APIConstants::ACCOUNTS];
            }
            if (isset($convertedIdsJSON[APIConstants::DEALS]) && null != $convertedIdsJSON[APIConstants::DEALS]) {
                $convertedIds[APIConstants::DEALS] = $convertedIdsJSON[APIConstants::DEALS];
            }

            return $convertedIds;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function uploadPhoto($filePath)
    {
        try {
            if (function_exists('curl_file_create')) { // php 5.6+
                $cFile = curl_file_create($filePath);
            } else {
                $cFile = '@' . realpath($filePath);
            }
            $post = [
                'file' => $cFile,
            ];
            $this->requestMethod = APIConstants::REQUEST_METHOD_POST;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId() . '/photo';
            $this->requestBody = $post;
            $responseInstance = APIRequest::getInstance($this)->getAPIResponse();

            return $responseInstance;
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function downloadPhoto()
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_GET;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId() . '/photo';

            return APIRequest::getInstance($this)->downloadFile();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function deletePhoto()
    {
        try {
            $this->requestMethod = APIConstants::REQUEST_METHOD_DELETE;
            $this->urlPath = $this->record->getModuleApiName() . '/' . $this->record->getEntityId() . '/photo';

            return APIRequest::getInstance($this)->getAPIResponse();
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
    }

    public function getZCRMRecordAsJSON()
    {
        $recordJSON = [];
        $apiNameVsValues = $this->record->getData();
        if (null != $this->record->getOwner()) {
            $recordJSON['Owner'] = '' . $this->record->getOwner()->getId();
        }
        if (null != $this->record->getLayout()) {
            $recordJSON['Layout'] = '' . $this->record->getLayout()->getId();
        }
        foreach ($apiNameVsValues as $key => $value) {
            if ($value instanceof ZCRMRecord) {
                $value = '' . $value->getEntityId();
            } elseif ($value instanceof ZCRMUser) {
                $value = '' . $value->getId();
            }
            $recordJSON[$key] = $value;
        }
        if (sizeof($this->record->getLineItems()) > 0) {
            $recordJSON['Product_Details'] = self::getLineItemJSON($this->record->getLineItems());
        }
        if (sizeof($this->record->getParticipants()) > 0) {
            $recordJSON['Participants'] = self::getParticipantsAsJSONArray();
        }
        if (sizeof($this->record->getPriceDetails()) > 0) {
            $recordJSON['Pricing_Details'] = self::getPriceDetailsAsJSONArray();
        }
        if (sizeof($this->record->getTaxList()) > 0) {
            if ('Products' == $this->record->getModuleApiName()) {
                $key = 'Tax';
            } else {
                $key = '$line_tax';
            }

            $recordJSON[$key] = self::getTaxListAsJSON($key);
        }

        return $recordJSON;
    }

    public function getTaxListAsJSON($key)
    {
        $taxes = [];
        $taxList = $this->record->getTaxList();
        if ('Tax' == $key) {
            foreach ($taxList as $taxIns) {
                array_push($taxes, $taxIns->getTaxName());
            }
        } else {
            foreach ($taxList as $lineTaxInstance) {
                $tax = [];
                $tax['name'] = $lineTaxInstance->getTaxName();
                $tax['value'] = $lineTaxInstance->getValue();
                $tax['percentage'] = $lineTaxInstance->getPercentage();
                array_push($taxes, $tax);
            }
        }

        return $taxes;
    }

    public function getPriceDetailsAsJSONArray()
    {
        $priceDetailsArr = [];
        $priceDetailsList = $this->record->getPriceDetails();
        foreach ($priceDetailsList as $priceDetailIns) {
            array_push($priceDetailsArr, self::getZCRMPriceDetailAsJSON($priceDetailIns));
        }

        return $priceDetailsArr;
    }

    public function getZCRMPriceDetailAsJSON(ZCRMPriceBookPricing $priceDetailIns)
    {
        $priceDetailJSON = [];
        if (null != $priceDetailIns->getId()) {
            $priceDetailJSON['id'] = $priceDetailIns->getId();
        }
        $priceDetailJSON['discount'] = $priceDetailIns->getDiscount();
        $priceDetailJSON['to_range'] = $priceDetailIns->getToRange();
        $priceDetailJSON['from_range'] = $priceDetailIns->getFromRange();

        return $priceDetailJSON;
    }

    public function getParticipantsAsJSONArray()
    {
        $participantsArr = [];
        $participantsList = $this->record->getParticipants();
        foreach ($participantsList as $participantIns) {
            array_push($participantsArr, self::getZCRMParticipantAsJSON($participantIns));
        }

        return $participantsArr;
    }

    public function getZCRMParticipantAsJSON(ZCRMEventParticipant $participantIns)
    {
        $participantJSON = [];
        $participantJSON['participant'] = '' . $participantIns->getId();
        $participantJSON['type'] = '' . $participantIns->getType();
        $participantJSON['name'] = '' . $participantIns->getName();
        $participantJSON['Email'] = '' . $participantIns->getEmail();
        $participantJSON['invited'] = (bool) $participantIns->isInvited();
        $participantJSON['status'] = '' . $participantIns->getStatus();

        return $participantJSON;
    }

    public function getLineItemJSON($lineItemsArray)
    {
        $lineItemsAsJSONArray = [];
        foreach ($lineItemsArray as $lineItem) {
            $lineItemData = [];
            if (null == $lineItem->getQuantity()) {
                throw new ZCRMException("Mandatory Field 'quantity' is missing.", APIConstants::RESPONSECODE_BAD_REQUEST);
            }
            if (null != $lineItem->getId()) {
                $lineItemData['id'] = '' . $lineItem->getId();
            }
            if (null != $lineItem->getProduct()) {
                $lineItemData['product'] = '' . $lineItem->getProduct()->getEntityId();
            }
            if (null != $lineItem->getDescription()) {
                $lineItemData['product_description'] = $lineItem->getDescription();
            }
            if (null !== $lineItem->getListPrice()) {
                $lineItemData['list_price'] = $lineItem->getListPrice();
            }
            $lineItemData['quantity'] = $lineItem->getQuantity();
            /*
             * Either discount percentage can be 0 or discount value can be 0. So if percentage is 0, set value and vice versa.
             * If the intended discount is 0, then both percent and value will be 0. Hence setting either of this to 0, will be enough.
             */
            if (null == $lineItem->getDiscountPercentage()) {
                $lineItemData['Discount'] = $lineItem->getDiscount();
            } else {
                $lineItemData['Discount'] = $lineItem->getDiscountPercentage() . '%';
            }
            $lineTaxes = $lineItem->getLineTax();
            $lineTaxArray = [];
            foreach ($lineTaxes as $lineTaxInstance) {
                $tax = [];
                $tax['name'] = $lineTaxInstance->getTaxName();
                $tax['value'] = $lineTaxInstance->getValue();
                $tax['percentage'] = $lineTaxInstance->getPercentage();
                array_push($lineTaxArray, $tax);
            }
            $lineItemData['line_tax'] = $lineTaxArray;

            array_push($lineItemsAsJSONArray, array_filter($lineItemData, 'zcrmsdk\crm\utility\CommonUtil::removeNullValuesAlone'));
        }

        return array_filter($lineItemsAsJSONArray);
    }

    public function setRecordProperties($recordDetails)
    {
        foreach ($recordDetails as $key => $value) {
            if ('id' == $key) {
                $this->record->setEntityId($value);
            } elseif ('Product_Details' == $key && in_array($this->record->getModuleApiName(), APIConstants::INVENTORY_MODULES)) {
                $this->setInventoryLineItems($value);
            } elseif ('Participants' == $key && 'Events' == $this->record->getModuleApiName()) {
                $this->setParticipants($value);
            } elseif ('Pricing_Details' == $key && 'Price_Books' == $this->record->getModuleApiName()) {
                $this->setPriceDetails($value);
            } elseif ('Created_By' == $key) {
                $createdBy = ZCRMUser::getInstance($value['id'], $value['name']);
                $this->record->setCreatedBy($createdBy);
            } elseif ('Modified_By' == $key) {
                $modifiedBy = ZCRMUser::getInstance($value['id'], $value['name']);
                $this->record->setModifiedBy($modifiedBy);
            } elseif ('Created_Time' == $key) {
                $this->record->setCreatedTime('' . $value);
            } elseif ('Modified_Time' == $key) {
                $this->record->setModifiedTime('' . $value);
            } elseif ('Last_Activity_Time' == $key) {
                $this->record->setLastActivityTime('' . $value);
            } elseif ('Owner' == $key) {
                $owner = ZCRMUser::getInstance($value['id'], $value['name']);
                $this->record->setOwner($owner);
            } elseif ('Layout' == $key) {
                $layout = null;
                if (null != $value) {
                    $layout = ZCRMLayout::getInstance($value['id']);
                    $layout->setName($value['name']);
                }
                $this->record->setLayout($layout);
            } elseif ('Handler' == $key && null != $value) {
                $handler = ZCRMUser::getInstance($value['id'], $value['name']);
                $this->record->setFieldValue($key, $handler);
            } elseif ('Tax' === $key && is_array($value)) {
                foreach ($value as $taxName) {
                    $taxIns = ZCRMTax::getInstance($taxName);
                    $this->record->addTax($taxIns);
                }
            } elseif ('Tag' === $key && is_array($value)) {
                $tags = [];
                foreach ($value as $tag) {
                    $tagIns = ZCRMTag::getInstance($tag['id'], $tag['name']);
                    array_push($tags, $tagIns);
                }
                $this->record->setTags($tags);
            } elseif ('tags' === $key && is_array($value)) {
                $this->record->setTagNames($value);
            } elseif ('$line_tax' === $key && is_array($value)) {
                foreach ($value as $lineTax) {
                    $taxIns = ZCRMTax::getInstance($lineTax['name']);
                    $taxIns->setPercentage($lineTax['percentage']);
                    $taxIns->setValue($lineTax['value']);
                    $this->record->addTax($taxIns);
                }
            } elseif ('$' == substr($key, 0, 1)) {
                $this->record->setProperty(str_replace('$', '', $key), $value);
            } elseif (is_array($value)) {
                if (isset($value['id'])) {
                    $lookupRecord = ZCRMRecord::getInstance($key, isset($value['id']) ? $value['id'] : '0');
                    $lookupRecord->setLookupLabel(isset($value['name']) ? $value['name'] : null);
                    $this->record->setFieldValue($key, $lookupRecord);
                } else {
                    $this->record->setFieldValue($key, $value);
                }
            } else {
                $this->record->setFieldValue($key, $value);
            }
        }
    }

    private function setParticipants($participants)
    {
        foreach ($participants as $participantDetail) {
            $this->record->addParticipant(self::getZCRMParticipant($participantDetail));
        }
    }

    private function setPriceDetails($priceDetails)
    {
        foreach ($priceDetails as $priceDetail) {
            $this->record->addPriceDetail(self::getZCRMPriceDetail($priceDetail));
        }
    }

    public function getZCRMParticipant($participantDetail)
    {
        $id = null;
        $email = null;
        if (array_key_exists('Email', $participantDetail)) {
            $email = $participantDetail['Email'];
            $id = $participantDetail['participant'];
        } else {
            $email = $participantDetail['participant'];
        }
        $participant = ZCRMEventParticipant::getInstance($participantDetail['type'], $id);
        $participant->setName($participantDetail['name']);
        $participant->setEmail($email);
        $participant->setInvited((bool) $participantDetail['invited']);
        $participant->setStatus($participantDetail['status']);

        return $participant;
    }

    public function getZCRMPriceDetail($priceDetails)
    {
        $priceDetailIns = ZCRMPriceBookPricing::getInstance($priceDetails['id']);
        $priceDetailIns->setDiscount((float) $priceDetails['discount']);
        $priceDetailIns->setToRange((float) $priceDetails['to_range']);
        $priceDetailIns->setFromRange((float) $priceDetails['from_range']);

        return $priceDetailIns;
    }

    public function setInventoryLineItems($lineItems)
    {
        foreach ($lineItems as $lineItem) {
            $this->record->addLineItem(self::getZCRMLineItemInstance($lineItem));
        }
    }

    public function getZCRMLineItemInstance($lineItemDetails)
    {
        $productDetails = $lineItemDetails['product'];
        $lineItemId = $lineItemDetails['id'];
        $lineItemInstance = ZCRMInventoryLineItem::getInstance($lineItemId);
        $product = ZCRMRecord::getInstance('Products', $productDetails['id']);
        $product->setLookupLabel($productDetails['name']);
        if (isset($productDetails['Product_Code'])) {
            $product->setFieldValue('Product_Code', $productDetails['Product_Code']);
        }
        $lineItemInstance->setProduct($product);
        $lineItemInstance->setDescription($lineItemDetails['product_description']);
        $lineItemInstance->setQuantity($lineItemDetails['quantity'] + 0);
        $lineItemInstance->setListPrice($lineItemDetails['list_price'] + 0);
        $lineItemInstance->setTotal($lineItemDetails['total'] + 0);
        $lineItemInstance->setDiscount($lineItemDetails['Discount'] + 0);
        $lineItemInstance->setTotalAfterDiscount($lineItemDetails['total_after_discount'] + 0);
        $lineItemInstance->setTaxAmount($lineItemDetails['Tax'] + 0);
        $lineTaxes = $lineItemDetails['line_tax'];
        foreach ($lineTaxes as $lineTax) {
            $taxInstance = ZCRMTax::getInstance($lineTax['name']);
            $taxInstance->setPercentage($lineTax['percentage']);
            $taxInstance->setValue($lineTax['value'] + 0);
            $lineItemInstance->addLineTax($taxInstance);
        }
        $lineItemInstance->setNetTotal($lineItemDetails['net_total'] + 0);

        return $lineItemInstance;
    }
}
