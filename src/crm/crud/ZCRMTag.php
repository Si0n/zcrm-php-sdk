<?php

namespace zcrmsdk\crm\crud;

use zcrmsdk\crm\api\handler\TagAPIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;

class ZCRMTag
{
    private null|ZCRMUser $createdBy = null;

    private null|string $createdTime = null;

    private null|ZCRMUser $modifiedBy = null;

    private null|string $modifiedTime = null;

    /**
     * number of record tagged.
     */
    private null|int $count = null;

    /**
     * api name of the module to which the tag belongs.
     */
    private null|string $moduleAPIName = null;

    /**
     * constructor to assign tag id and module api name to the tag.
     *
     * @param string $id   tag id
     * @param string $name tag name
     */
    private function __construct(protected null|string $id, protected null|string $name)
    {
    }

    /**
     * method to get the instance of the tag.
     *
     * @param string $id   tag id (default can be null)
     * @param string $name tag name (default can be null)
     *
     * @return ZCRMTag instance of the ZCRMTag class
     */
    public static function getInstance(null|string $id = null, null|string $name = null): ZCRMTag
    {
        return new ZCRMTag($id, $name);
    }

    /**
     * method to get the tag id.
     *
     * @return string the tag id
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * method to set the tag id.
     *
     * @param string $id the tag id
     */
    public function setId(null|string $id): void
    {
        $this->id = $id;
    }

    /**
     * method to get the tag name.
     *
     * @return string the tag name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * method to set the tag name.
     *
     * @param string $name the tag name
     */
    public function setName(null|string $name): void
    {
        $this->name = $name;
    }

    /**
     * method to get the user who created the tag.
     *
     * @return ZCRMUser instance of the ZCRMUser class
     */
    public function getCreatedBy(): ?ZCRMUser
    {
        return $this->createdBy;
    }

    /**
     * method to set the user who created the tag.
     *
     * @param ZCRMUser $createdBy instance of the ZCRMUser class
     */
    public function setCreatedBy(null|ZCRMUser $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * method to get the user who modified the tag.
     *
     * @return ZCRMUser instance of the ZCRMUser class
     */
    public function getModifiedBy(): ?ZCRMUser
    {
        return $this->modifiedBy;
    }

    /**
     * method to set the user who modified the tag.
     *
     * @param ZCRMUser $modifiedBy instance of the ZCRMUser class
     */
    public function setModifiedBy(null|ZCRMUser $modifiedBy): void
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * method to get the tag creation time.
     *
     * @return string tag creation time in iso 8601 format
     */
    public function getCreatedTime(): ?string
    {
        return $this->createdTime;
    }

    /**
     * method to set the tag creation time.
     *
     * @param string $createdTime creation time in iso 8601 format
     */
    public function setCreatedTime(null|string $createdTime): void
    {
        $this->createdTime = $createdTime;
    }

    /**
     * method to get the tag modification time.
     *
     * @return string modification time in iso 8601 format
     */
    public function getModifiedTime(): ?string
    {
        return $this->modifiedTime;
    }

    /**
     * method to set the tag modification time.
     *
     * @param string $modifiedTime modification time in iso 8601 format
     */
    public function setModifiedTime(null|string $modifiedTime): void
    {
        $this->modifiedTime = $modifiedTime;
    }

    /**
     * method to get the record count of the tag.
     *
     * @return int record count of the tag
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * method to set the record count of the tag.
     *
     * @param int $count record count of the tag
     */
    public function setCount(null|int $count): void
    {
        $this->count = $count;
    }

    /**
     * method to get the module api name of the module to which tag belongs.
     *
     * @return string module api name of the module to which tag belongs
     */
    public function getModuleAPIName(): ?string
    {
        return $this->moduleAPIName;
    }

    /**
     * method to set the module api name of the module to which tag belongs.
     */
    public function setModuleAPIName(null|string $moduleAPIName): void
    {
        $this->moduleAPIName = $moduleAPIName;
    }

    /**
     * method to delete the tag.
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException if tag is invalid
     */
    public function delete(): APIResponse
    {
        if (null == $this->id || '' == $this->id) {
            throw new ZCRMException('Tag ID MUST NOT be null/empty for delete operation');
        }

        return TagAPIHandler::getInstance()->delete($this->id);
    }

    /**
     * method to merge the tags.
     *
     * @param ZCRMTag $tag tag to be merged with
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException if tags are invalid
     */
    public function merge(ZCRMTag $tag): APIResponse
    {
        if (null == $this->id || '' == $this->id) {
            throw new ZCRMException('Tag ID MUST NOT be null/empty for merge operation');
        }
        if (null == $tag->id || 0 == $tag->id) {
            throw new ZCRMException('Merge Tag ID MUST NOT be null/empty for merge operation');
        }

        return TagAPIHandler::getInstance()->merge($this->id, $tag->id);
    }

    /**
     * method to update the tag.
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException if the tag id , tag name or the ,odule api name is invalid
     */
    public function update(): APIResponse
    {
        if (null == $this->id || '' == $this->id) {
            throw new ZCRMException('Tag ID MUST NOT be null/empty for update operation');
        }
        if (null == $this->moduleAPIName || '' == $this->moduleAPIName) {
            throw new ZCRMException('Module Api Name MUST NOT be null/empty for update operation');
        }
        if (null == $this->name || '' == $this->name) {
            throw new ZCRMException('Tag Name MUST NOT be null/empty for update operation');
        }

        return TagAPIHandler::getInstance()->update($this);
    }
}
