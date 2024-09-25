<?php

namespace zcrmsdk\crm\crud;

use zcrmsdk\crm\api\handler\TagAPIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;

class ZCRMTag
{
    /**
     * tag id.
     *
     * @var string
     */
    private $id;

    /**
     * tag name.
     *
     * @var string
     */
    private $name;

    /**
     * creator of the tag.
     *
     * @var ZCRMUser
     */
    private $createdBy;

    /**
     * creation time of the tag.
     *
     * @var string
     */
    private $createdTime;

    /**
     * modifier of the tag.
     *
     * @var ZCRMUser
     */
    private $modifiedBy;

    /**
     * modification time of the tag.
     *
     * @var string
     */
    private $modifiedTime;

    /**
     * number of record tagged.
     *
     * @var int
     */
    private $count;

    /**
     * api name of the module to which the tag belongs.
     *
     * @var string
     */
    private $moduleAPIName;

    /**
     * constructor to assign tag id and module api name to the tag.
     *
     * @param string $id   tag id
     * @param string $name tag name
     */
    private function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * method to get the instance of the tag.
     *
     * @param string $id   tag id (default can be null)
     * @param string $name tag name (default can be null)
     *
     * @return ZCRMTag instance of the ZCRMTag class
     */
    public static function getInstance($id = null, $name = null)
    {
        return new ZCRMTag($id, $name);
    }

    /**
     * method to get the tag id.
     *
     * @return string the tag id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * method to set the tag id.
     *
     * @param string $id the tag id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * method to get the tag name.
     *
     * @return string the tag name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * method to set the tag name.
     *
     * @param string $name the tag name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * method to get the user who created the tag.
     *
     * @return ZCRMUser instance of the ZCRMUser class
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * method to set the user who created the tag.
     *
     * @param ZCRMUser $createdBy instance of the ZCRMUser class
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * method to get the user who modified the tag.
     *
     * @return ZCRMUser instance of the ZCRMUser class
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * method to set the user who modified the tag.
     *
     * @param ZCRMUser $modifiedBy instance of the ZCRMUser class
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * method to get the tag creation time.
     *
     * @return string tag creation time in iso 8601 format
     */
    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    /**
     * method to set the tag creation time.
     *
     * @param string $createdTime creation time in iso 8601 format
     */
    public function setCreatedTime($createdTime)
    {
        $this->createdTime = $createdTime;
    }

    /**
     * method to get the tag modification time.
     *
     * @return string modification time in iso 8601 format
     */
    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    /**
     * method to set the tag modification time.
     *
     * @param string $modifiedTime modification time in iso 8601 format
     */
    public function setModifiedTime($modifiedTime)
    {
        $this->modifiedTime = $modifiedTime;
    }

    /**
     * method to get the record count of the tag.
     *
     * @return int record count of the tag
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * method to set the record count of the tag.
     *
     * @param int $count record count of the tag
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * method to get the module api name of the module to which tag belongs.
     *
     * @return string module api name of the module to which tag belongs
     */
    public function getModuleAPIName()
    {
        return $this->moduleAPIName;
    }

    /**
     * method to set the module api name of the module to which tag belongs.
     */
    public function setModuleAPIName($moduleAPIName)
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
    public function delete()
    {
        if (null == $this->id || '' == $this->id) {
            throw new ZCRMException('Tag ID MUST NOT be null/empty for delete operation');
        }

        return TagAPIHandler::getInstance()->delete($this->id);
    }

    /**
     * method to merge the tags.
     *
     * @param ZCRMTag $mergetag tag to be merged with
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException if tags are invalid
     */
    public function merge($mergetag)
    {
        if (null == $this->id || '' == $this->id) {
            throw new ZCRMException('Tag ID MUST NOT be null/empty for merge operation');
        }
        if (null == $mergetag->id || 0 == $mergetag->id) {
            throw new ZCRMException('Merge Tag ID MUST NOT be null/empty for merge operation');
        }

        return TagAPIHandler::getInstance()->merge($this->id, $mergetag->id);
    }

    /**
     * method to update the tag.
     *
     * @return APIResponse instance of the APIResponse class containing the api response
     *
     * @throws ZCRMException if the tag id , tag name or the ,odule api name is invalid
     */
    public function update()
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
