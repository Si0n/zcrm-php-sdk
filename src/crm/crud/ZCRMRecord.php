<?php

namespace zcrmsdk\crm\crud;

use zcrmsdk\crm\api\handler\EntityAPIHandler;
use zcrmsdk\crm\api\handler\TagAPIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\api\response\BulkAPIResponse;
use zcrmsdk\crm\api\response\FileAPIResponse;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;

/**
 * Provides methods for basic CRUD operations of the record.
 *
 * @author sumanth-3058
 */
class ZCRMRecord
{
    /**
     * the inventory item list.
     */
    private array $lineItems = [];

    /**
     * the lookup label.
     */
    private ?string $lookupLabel= null;

    /**
     * the owner of the record.
     */
    private ?ZCRMUser $owner = null;

    /**
     * the user who created the record.
     */
    private ?ZCRMUser $createdBy = null;

    /**
     * the user who modified the record.
     */
    private ?ZCRMUser $modifiedBy = null;

    /**
     * creation time of the record.
     */
    private ?string $createdTime = null;

    /**
     * modification time of the record.
     */
    private ?string $modifiedTime = null;

    /**
     * the record data.
     */
    private array $fieldNameVsValue = [];

    /**
     * properties of the record.
     */
    private array $properties = [];

    /**
     * participants in the record.
     */
    private array $participants = [];

    /**
     * price detail of the product.
     */
    private array $priceDetails = [];

    /**
     * layout of the record.
     */
    private ?ZCRMLayout $layout = null;

    /**
     * the list of tax.
     */
    private array $taxList = [];

    /**
     * the time of the last activity done on the record.
     */
    private ?string $lastActivityTime = null;

    /**
     * list of all the tags.
     */
    private array $tags = [];

    /**
     * list of all the tag names.
     */
    private array $tagNames = [];

    /**
     * bulk write status of the record.
     */
    private ?string $status = null;

    /**
     * bulk write error message of the record.
     */
    private ?string $error = null;

    /**
     * csv record row number.
     */
    private ?int $rowNumber = null;

    private function __construct(protected null|string $moduleApiName, protected null|string $entityId)
    {
    }

    /**
     * Method to get the instance of the ZCRMRecord class.
     *
     * @param string $module   api name of the module
     * @param string $entityId the record id
     */
    public static function getInstance(null|string $module, null|string $entityId): ZCRMRecord
    {
        return new ZCRMRecord($module, $entityId);
    }

    /**
     * Method inserts the tax associated to the record.
     */
    public function addTax(ZCRMTax $taxIns): void
    {
        $this->taxList[] = $taxIns;
    }

    /**
     * Method to get the tax associated to the record.
     *
     * @return array array of ZCRMTax tax instances
     */
    public function getTaxList(): array
    {
        return $this->taxList;
    }

    /**
     * Method to get the record id.
     */
    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * Method to set the record id.
     */
    public function setEntityId(?string $entityId): void
    {
        $this->entityId = $entityId;
    }

    /**
     * Method to get the module api name of that record.
     *
     * @return string api name of the module
     */
    public function getModuleApiName(): ?string
    {
        return $this->moduleApiName;
    }

    /**
     * Method to set the module api name of the record.
     *
     * @param string $moduleApiName module api name of the record
     */
    public function setModuleApiName(?string $moduleApiName) : void
    {
        $this->moduleApiName = $moduleApiName;
    }

    /**
     * Method to get the field value by api name of the field of the record.
     */
    public function getFieldValue(string $apiName, mixed $defaultValue = null): mixed
    {
        return $this->fieldNameVsValue[$apiName] ?? $defaultValue;
    }

    /**
     * Method to set the field value by api name of the field of the record.
     */
    public function setFieldValue(string $apiName, mixed $value): void
    {
        $this->fieldNameVsValue[$apiName] = $value;
    }

    /**
     * Method to get an array(key-value pair) containing field name as key and field data as value for the record.
     *
     * @return array key-value pair of field name and field value
     */
    public function getData(): array
    {
        return $this->fieldNameVsValue;
    }

    /**
     * Method to get the line items of the inventory record.
     *
     * @return array<ZCRMInventoryLineItem>
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    /**
     * Method adds the line item to the inventory record.
     */
    public function addLineItem(ZCRMInventoryLineItem $lineItem): void
    {
        $this->lineItems[] = $lineItem;
    }

    /**
     * Method update the line item of the inventory record.
     *
     * @param ZCRMInventoryLineItem $updatedLineItem updated line item
     */
    public function updateLineItem(ZCRMInventoryLineItem $updatedLineItem): void
    {
        if (null == $updatedLineItem->getId()) {
            throw new ZCRMException('Line item id missing');
        }
        $this->removeLineItem($updatedLineItem->getId());
        $this->lineItems[] = $updatedLineItem;
    }

    public function removeLineItem(string $lineItemId): void
    {
        $found = false;
        /**
         * @var ZCRMInventoryLineItem $lineItem
         */
        foreach ($this->lineItems as $key => $lineItem) {
            if ($lineItemId !== $lineItem->getId()) {
                continue;
            }
            $found = true;
            unset($this->lineItems[$key]);
            break;
        }
        if (!$found) {
            throw new ZCRMException("Line item with such id doesn't exist");
        }
    }

    /**
     * Method to add Line item to existing record.
     */
    public function addLineItemToExistingRecord(ZCRMInventoryLineItem $lineItem)
    {
        $recordInstance = EntityAPIHandler::getInstance($this)->getRecord()->getData(); // returns ZCRMRecord object
        $recordInstance->addLineItem($lineItem);

        return $recordInstance->update();
    }

    /**
     * Method to update Line item from the existing record.
     *
     * @param ZCRMInventoryLineItem $updatedLineItem updated line item
     */
    public function updateLineItemOfTheExistingRecord(ZCRMInventoryLineItem $updatedLineItem)
    {
        $recordInstance = EntityAPIHandler::getInstance($this)->getRecord()->getData(); // returns ZCRMRecord object
        $recordInstance->updateLineItem($updatedLineItem);

        return $recordInstance->update();
    }

    /**
     * Method to update Line item from the existing record.
     *
     * @param string $lineItemId the line item id
     *
     * @oaram
     */
    public function deleteLineItemFromTheExistingRecord(string $lineItemId)
    {
        $recordInstance = EntityAPIHandler::getInstance($this)->getRecord()->getData(); // returns ZCRMRecord object
        $recordInstance->removeLineItem($lineItemId);

        return $recordInstance->update();
    }

    /**
     * Method to get the lookup label of the record.
     *
     * @return string -the look up label of the record
     */
    public function getLookupLabel(): ?string
    {
        return $this->lookupLabel;
    }

    /**
     * Method to set the lookup label for the record.
     *
     * @param string $lookupLabel lookup label that you want to set
     */
    public function setLookupLabel(?string $lookupLabel): void
    {
        $this->lookupLabel = $lookupLabel;
    }

    /**
     * Method to get the owner of the record.
     *
     * @return ZCRMUser owner of the record
     */
    public function getOwner(): ?ZCRMUser
    {
        return $this->owner;
    }

    /**
     * Method to set the owner of the record.
     *
     * @param ZCRMUser $owner owner of the record
     */
    public function setOwner(?ZCRMUser $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * Method to get the creator of that record.
     *
     * @return ZCRMUser user who created the record
     */
    public function getCreatedBy(): ?ZCRMUser
    {
        return $this->createdBy;
    }

    /**
     * Method to set the creator of that record.
     *
     * @param ZCRMUser $createdBy user who created the record
     */
    public function setCreatedBy(ZCRMUser $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * Method to get the user who modified the record.
     *
     * @return ZCRMUser user who modified the record
     */
    public function getModifiedBy(): ?ZCRMUser
    {
        return $this->modifiedBy;
    }

    /**
     * Method to set the user who modified the record.
     *
     * @param ZCRMUser $modifiedBy user who modified the record
     */
    public function setModifiedBy(?ZCRMUser $modifiedBy): void
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * Method to get the creation time of the record.
     *
     * @return string creation time in ISO 8601 format
     */
    public function getCreatedTime() :?string
    {
        return $this->createdTime;
    }

    /**
     * Method to set the creation time of the record.
     *
     * @param string $createdTime creation time in ISO 8601 format
     */
    public function setCreatedTime(?string $createdTime): void
    {
        $this->createdTime = $createdTime;
    }

    /**
     * Method to get the modification time of the record.
     *
     * @return string the modification time in ISO 8601 format
     */
    public function getModifiedTime(): ?string
    {
        return $this->modifiedTime;
    }

    /**
     * Method to set the modification time of the record.
     *
     * @param string $modifiedTime modification time in ISO 8601 format
     */
    public function setModifiedTime(?string $modifiedTime) :void
    {
        $this->modifiedTime = $modifiedTime;
    }

    /**
     * Method to get the tags for the record.
     *
     * @return array<ZCRMTag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Method to set the tags for the record.
     *
     * @param array<ZCRMTag> $tags array of ZCRMTag instances related to the record
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * Method to get the tags for the record.
     *
     * @return array array of tag name of the record
     */
    public function getTagNames(): array
    {
        return $this->tagNames;
    }

    /**
     * Method to set the tags for the record.
     *
     * @param array $tagNames array of tag name of the record
     */
    public function setTagNames(array $tagNames) :void
    {
        $this->tagNames = $tagNames;
    }

    /**
     * To set create record status.
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * To get create record status.
     *
     * @return string status of the record
     */
    public function getStatus():?string
    {
        return $this->status;
    }

    /**
     * To set record error message.
     */
    public function setErrorMessage(?string $error): void
    {
        $this->error = $error;
    }

    /**
     * To get record error message.
     */
    public function getErrorMessage(): ?string
    {
        return $this->error;
    }

    /**
     * To set record row number.
     */
    public function setRecordRowNumber(?int $rowNumber): void
    {
        $this->rowNumber = $rowNumber;
    }

    /**
     * To get record row number.
     *
     * @return int record row number
     */
    public function getRecordRowNumber(): ?int
    {
        return $this->rowNumber;
    }

    /**
     * Method creates record.
     *
     * @param string $lar_id lead assignment rule id
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if Entity ID of the record is not NULL
     */
    public function create(?array $trigger = null, ?string $lar_id = null, ?array $process = null): APIResponse
    {
        if (null != self::getEntityId()) {
            $exception = new ZCRMException('Entity ID MUST be null for create operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            $exception->setExceptionCode('ID EXIST');
            throw $exception;
        }

        return EntityAPIHandler::getInstance($this)->createRecord($trigger, $lar_id, $process);
    }

    /**
     * Method to update the records.
     *
     ** @param string $trigger array of triggers
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if Entity ID of the record is NULL
     */
    public function update(?array $trigger = null, ?array $process = null): APIResponse
    {
        if (null == self::getEntityId()) {
            $exception = new ZCRMException('Entity ID MUST NOT be null for update operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            $exception->setExceptionCode('ID MISSING');
            throw $exception;
        }

        return EntityAPIHandler::getInstance($this)->updateRecord($trigger, $process);
    }

    /**
     * Method to delete the record.
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if Entity ID of the record is NULL
     */
    public function delete(): APIResponse
    {
        if (null == self::getEntityId()) {
            $exception = new ZCRMException('Entity ID MUST NOT be null for delete operation.', APIConstants::RESPONSECODE_BAD_REQUEST);
            $exception->setExceptionCode('ID MISSING');
            throw $exception;
        }

        return EntityAPIHandler::getInstance($this)->deleteRecord();
    }

    /**
     * Method to convert the record.
     *
     * @param ZCRMRecord $potentialRecord the potential record
     */
    public function convert(?ZCRMRecord $potentialRecord = null, $details = null): array
    {
        return EntityAPIHandler::getInstance($this)->convertRecord($potentialRecord, $details);
    }

    /**
     * Method to get the RelatedList records.
     *
     * @param string $relatedListAPIName Api name of the Related List
     * @param array  $param_map          key-value pairs containing parameters
     * @param array  $header_map         key-value pairs containing headers
     *
     * @return BulkAPIResponse instance of the BulkAPIResponse class which holds the Bulk API Response
     */
    public function getRelatedListRecords($relatedListAPIName, $param_map = [], $header_map = [])
    {
        return ZCRMModuleRelation::getInstance($this, $relatedListAPIName)->getRecords($param_map, $header_map);
    }

    /**
     * Method to get the notes.
     *
     * @param array $param_map  key-value pairs containing parameters
     * @param array $header_map key-value pairs containing headers
     *
     * @return BulkAPIResponse instance of the BulkAPIResponse class which holds the Bulk API response
     */
    public function getNotes($param_map = [], $header_map = [])
    {
        return ZCRMModuleRelation::getInstance($this, 'Notes')->getNotes($param_map, $header_map);
    }

    /**
     * Method adds the note to the record.
     *
     * @param ZCRMNote $zcrmNoteIns note instance
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if the note id of the note is null
     */
    public function addNote($zcrmNoteIns)
    {
        if (null != $zcrmNoteIns->getId()) {
            $exception = new ZCRMException('Note ID MUST be null for creating a note.', APIConstants::RESPONSECODE_BAD_REQUEST);
            $exception->setExceptionCode('ID EXIST');
            throw $exception;
        }

        return ZCRMModuleRelation::getInstance($this, 'Notes')->addNote($zcrmNoteIns);
    }

    public function addNotes($noteInstances)
    {
        return ZCRMModuleRelation::getInstance($this, 'Notes')->addNotes($noteInstances);
    }

    /**
     * Method to update the note of the reecord.
     *
     * @param ZCRMNote $zcrmNoteIns Notes instance
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if note instance is not valid
     */
    public function updateNote($zcrmNoteIns)
    {
        if (null == $zcrmNoteIns->getId()) {
            $exception = new ZCRMException('Note ID MUST NOT be null for updating a note.', APIConstants::RESPONSECODE_BAD_REQUEST);
            $exception->setExceptionCode('ID MISSING');
            throw $exception;
        }

        return ZCRMModuleRelation::getInstance($this, 'Notes')->updateNote($zcrmNoteIns);
    }

    /**
     * Method to delete the note of the record.
     *
     * @param ZCRMNote $zcrmNoteIns note instance
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if note id is not valid
     */
    public function deleteNote($zcrmNoteIns)
    {
        if (null == $zcrmNoteIns->getId()) {
            $exception = new ZCRMException('Note ID MUST NOT be null for deleting a note.', APIConstants::RESPONSECODE_BAD_REQUEST);
            $exception->setExceptionCode('ID MISSING');
            throw $exception;
        }

        return ZCRMModuleRelation::getInstance($this, 'Notes')->deleteNote($zcrmNoteIns);
    }

    /**
     * Method to get the attachments of the record.
     *
     * @param array $param_map key-value pairs containing parameters
     *
     * @return BulkAPIResponse instance of the BulkAPIResponse class which holds the BulkAPI response
     */
    public function getAttachments($param_map = [])
    {
        return ZCRMModuleRelation::getInstance($this, 'Attachments')->getAttachments($param_map);
    }

    /**
     * Method to upload the attachment to the record.
     *
     * @param string $filePath the file path of the attachment
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     */
    public function uploadAttachment(string $filePath): APIResponse
    {
        return ZCRMModuleRelation::getInstance($this, 'Attachments')->uploadAttachment($filePath);
    }

    /**
     * Method to upload the link as the attachment to the record.
     *
     * @param string $attachmentUrl the URL of the attachment
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     */
    public function uploadLinkAsAttachment(string $attachmentUrl, ?string $title = null): APIResponse
    {
        return ZCRMModuleRelation::getInstance($this, 'Attachments')->uploadLinkAsAttachment($attachmentUrl, $title);
    }

    /**
     * Method to download the attachment of the record.
     *
     * @param string $attachmentId the attachment id
     *
     * @return FileAPIResponse instance of the FileAPIResponse class which holds the response
     */
    public function downloadAttachment(string $attachmentId): FileAPIResponse
    {
        return ZCRMModuleRelation::getInstance($this, 'Attachments')->downloadAttachment($attachmentId);
    }

    /**
     * Method to delete the attachment of the record.
     *
     * @param string $attachmentId the attachment id
     *
     * @return APIResponse instance of the APIResponse class which holds the response
     */
    public function deleteAttachment(string $attachmentId): APIResponse
    {
        return ZCRMModuleRelation::getInstance($this, 'Attachments')->deleteAttachment($attachmentId);
    }

    /**
     * Method to upload a photo to the record.
     *
     * @param string $filePath the location of the photo
     *
     * @return APIResponse instance of the APIResponse class which holds the response
     */
    public function uploadPhoto($filePath)
    {
        return EntityAPIHandler::getInstance($this)->uploadPhoto($filePath);
    }

    /**
     * Method to download the photo of the record.
     *
     * @return FileAPIResponse instance of the FileAPIResponse class which holds the response
     */
    public function downloadPhoto()
    {
        return EntityAPIHandler::getInstance($this)->downloadPhoto();
    }

    /**
     * Method to delete the photo of the record.
     *
     * @return APIResponse instance of the APIResponse class which holds the response
     */
    public function deletePhoto()
    {
        return EntityAPIHandler::getInstance($this)->deletePhoto();
    }

    /**
     * Method to relate the record with another record.
     *
     * @param ZCRMJunctionRecord $junctionRecord instance of ZCRMJunctionRecord class with which relation has to be created
     *
     * @return APIResponse APIResponse instance of the APIResponse class which holds the API response
     */
    public function addRelation(ZCRMJunctionRecord $junctionRecord)
    {
        return ZCRMModuleRelation::getInstance($this, $junctionRecord)->addRelation();
    }

    /**
     * Method to delete the relationship between the records.
     *
     * @param ZCRMJunctionRecord $junctionRecord instance of ZCRMJunctionRecord class which relation has to be removed
     *
     * @return APIResponse APIResponse instance of the APIResponse class which holds the API response
     */
    public function removeRelation(ZCRMJunctionRecord $junctionRecord): APIResponse
    {
        return ZCRMModuleRelation::getInstance($this, $junctionRecord)->removeRelation();
    }

    /**
     * Method adds the tag to the record.
     *
     * @param string $tagNames tagnames to add(multiple tag names as comma separated values)
     *
     * @return APIResponse APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if the record or module or tag doesn't exist
     */
    public function addTags(array $tagNames): APIResponse
    {
        if (null == $this->entityId || '' == $this->entityId) {
            throw new ZCRMException('Record ID MUST NOT be null/empty for Add Tags to a Specific record operation');
        }
        if (null == $this->moduleApiName || '' == $this->moduleApiName) {
            throw new ZCRMException('Module Api Name MUST NOT be null/empty for Add Tags to a Specific record operation');
        }
        if (sizeof($tagNames) <= 0) {
            throw new ZCRMException('Tag Name list MUST NOT be null/empty for Add Tags to a Specific record operation');
        }

        return TagAPIHandler::getInstance()->addTags($this, $tagNames);
    }

    /**
     * Method to remove the tags for the record.
     *
     * @param string $tagNames tag names to remove(multiple tag names as comma separated values)
     *
     * @return APIResponse instance of the APIResponse class which holds the API response
     *
     * @throws ZCRMException if the record or module or tag doesn't exist
     */
    public function removeTags(array $tagNames): APIResponse
    {
        if (null == $this->entityId || '' == $this->entityId) {
            throw new ZCRMException('Record ID MUST NOT be null/empty for Remove Tags from a Specific record operation');
        }
        if (null == $this->moduleApiName || '' == $this->moduleApiName) {
            throw new ZCRMException('Module Api Name MUST NOT be null/empty for Remove Tags from a Specific record operation');
        }
        if (sizeof($tagNames) <= 0) {
            throw new ZCRMException('Tag Name list MUST NOT be null/empty for Remove Tags from a Specific record operation');
        }

        return TagAPIHandler::getInstance()->removeTags($this, $tagNames);
    }

    /**
     * Method to get the properties of a record.
     *
     * @return array properties of the record
     */
    public function getAllProperties()
    {
        return $this->properties;
    }

    /**
     * Method to get the value of the property name of the record.
     *
     * @param string $propertyName name of the property
     *
     * @return string property value of the property name
     */
    public function getProperty($propertyName)
    {
        return $this->properties[$propertyName];
    }

    /**
     * Method to set the property value to the property name of the record.
     *
     * @param string $key   property name
     * @param string $value property value
     */
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * method to get the participants of the record.
     *
     * @return array array of ZCRMParticipants instances of the record
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * method to add the participants to the record.
     *
     * @param array $participant ZCRMParticipants instances of the record
     */
    public function addParticipant($participant)
    {
        array_push($this->participants, $participant);
    }

    /**
     * Method to fetch the price details of the record.
     *
     * @return array ZCRMPriceBookPricing instances in the record
     */
    public function getPriceDetails()
    {
        return $this->priceDetails;
    }

    /**
     * Method adds the price details to the record of the price book module.
     *
     * @param $priceDetail ZCRMPriceBookPricing pricing details of a ZCRMPriceBookPricing record
     */
    public function addPriceDetail($priceDetail)
    {
        array_push($this->priceDetails, $priceDetail);
    }

    public function getLayout(): ?ZCRMLayout
    {
        return $this->layout;
    }

    public function setLayout(?ZCRMLayout $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Method to get the time of last activity on the record.
     *
     * @return string Time of last activity
     */
    public function getLastActivityTime()
    {
        return $this->lastActivityTime;
    }

    /**
     * Method to set the time of last activity on the record.
     *
     * @param string $lastActivityTime Time of last activity
     */
    public function setLastActivityTime($lastActivityTime)
    {
        $this->lastActivityTime = $lastActivityTime;
    }
}
