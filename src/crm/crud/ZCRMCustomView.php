<?php

namespace zcrmsdk\crm\crud;

use zcrmsdk\crm\api\response\BulkAPIResponse;

class ZCRMCustomView
{
    /**
     * api name of the module.
     *
     * @var string api name of the module
     */
    private $moduleAPIName;

    /**
     * display name of the view.
     *
     * @var string
     */
    private $displayValue;

    /**
     * default view.
     *
     * @var bool
     */
    private $default;

    /**
     * custom view id.
     *
     * @var string
     */
    private $id;

    /**
     * custom view name.
     *
     * @var string
     */
    private $name;

    /**
     * custom view system name.
     *
     * @var string
     */
    private $systemName;

    /**
     * field api name.
     */
    private null|string $sortBy = null;

    /**
     * category of the custom view.
     */
    private null|ZCRMCustomViewCategory $category = null;

    /**
     * fields of the custom view.
     */
    private array $fields = [];

    /**
     * the favourite.
     */
    private null|bool $favorite = null;

    /**
     * the order of sorting of records in the view.
     */
    private null|string $sortOrder = null;

    /**
     * criteria pattern of the view.
     */
    private null|string $criteriaPattern = null;

    /**
     * record selection criteria.
     */
    private null|ZCRMCustomViewCriteria $criteria = null;
    /**
     * criteria condition.
     */
    private null|string $criteriaCondition = null;
    /**
     * category list of the view.
     *
     * @var array array instances of ZCRMCustomViewCategory
     */
    private array $categoriesList = [];

    /**
     * offline status of the view.
     */
    private null|bool $offLine = null;

    /**
     * constructor to set the module API name and custom view id.
     *
     * @param string $moduleAPIName module API name
     * @param string $id            module API name
     */
    public function __construct(null|string $moduleAPIName, null|string $id)
    {
        $this->moduleAPIName = $moduleAPIName;
        $this->id = $id;
    }

    /**
     * Method to get the instance of the ZCRMCustomView class.
     *
     * @param string $moduleAPIName module API name
     * @param string $id            module API name
     *
     * @return ZCRMCustomView instance of ZCRMCustomView class
     */
    public static function getInstance(null|string $moduleAPIName, null|string $id): ZCRMCustomView
    {
        return new ZCRMCustomView($moduleAPIName, $id);
    }

    /**
     * Method to get the display Name of the custom View.
     *
     * @return string display name of the custom view
     */
    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }

    /**
     * Method to set the display Name of the custom View.
     *
     * @param string $displayValue display name of the custom view
     */
    public function setDisplayValue(null|string $displayValue): void
    {
        $this->displayValue = $displayValue;
    }

    /**
     * Method to know whether the custom view is default or not.
     *
     * @return bool value true if default otherwise false
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * Method to set the custom view as default or not.
     *
     * @param bool $default true if default otherwise false
     */
    public function setDefault(null|bool $default): void
    {
        $this->default = $default;
    }

    /**
     * method to get the customview id.
     *
     * @return string custom view id
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * method to get the customview id.
     *
     * @param string $id custom view id
     */
    public function setId(null|string $id): void
    {
        $this->id = $id;
    }

    /**
     * Method to get the customview Name.
     *
     * @return string customview name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Method to set the customview Name.
     *
     * @param string $name customview name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Method to get the system name of the custom view.
     *
     * @return string system name of custom view
     */
    public function getSystemName(): string
    {
        return $this->systemName;
    }

    /**
     * Method to set the customview system Name.
     *
     * @param string $systemName system name of the custom view
     */
    public function setSystemName(null|string $systemName): void
    {
        $this->systemName = $systemName;
    }

    /**
     * Method to get the customview Sorted By field Name.
     *
     * @return string field api name
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * Method to set the customview Sorted By field Name.
     *
     * @param string $sortBy field api name
     */
    public function setSortBy(null|string $sortBy): void
    {
        $this->sortBy = $sortBy;
    }

    /**
     * Method to get the customview Category.
     */
    public function getCategory(): ZCRMCustomViewCategory|null
    {
        return $this->category;
    }

    /**
     * Method to set the customview Category.
     *
     * @param string $category custom view category
     */
    public function setCategory(null|ZCRMCustomViewCategory $category): void
    {
        $this->category = $category;
    }

    /**
     * Method to get the customview Fields.
     *
     * @return array array of field api name of the fields in custom view
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Method to set the customview Fields.
     *
     * @param array $fields array of field api name
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * Method to check whether the custom view is favourite one or not.
     */
    public function isFavorite(): bool|null
    {
        return $this->favorite;
    }

    /**
     * Method to set the custom view as favourite one or not.
     *
     * @param int $favorite favourite value
     */
    public function setFavorite(bool|null $favorite): void
    {
        $this->favorite = $favorite;
    }

    /**
     * Method to get the custom view records sort order.
     *
     * @return string sortorder (ascending if "asc" or descending if "desc")
     */
    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    /**
     * Method to set the custom view sort order type.
     *
     * @param string $sortOrder sorts the custom view records in ascending-"asc" or descending-"desc" order
     */
    public function setSortOrder(null|string $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Method to get the custom view criteria pattern.
     *
     * @return string CriteriaPattern criteria pattern
     */
    public function getCriteriaPattern(): ?string
    {
        return $this->criteriaPattern;
    }

    /**
     * Method to set the custom view criteria pattern.
     *
     * @param string $criteriaPattern Criteria pattern
     */
    public function setCriteriaPattern(null|string $criteriaPattern): void
    {
        $this->criteriaPattern = $criteriaPattern;
    }

    /**
     * Method to get the criteria of the custom view.
     */
    public function getCriteria(): ZCRMCustomViewCriteria|null
    {
        return $this->criteria;
    }

    /**
     * Method to set the criteria of the custom view.
     *
     * @param array $criteria array of instance of ZCRMCustomViewCriteria
     */
    public function setCriteria(ZCRMCustomViewCriteria $criteria): void
    {
        $this->criteria = $criteria;
    }

    public function getCriteriaCondition(): ?string
    {
        return $this->criteriaCondition;
    }

    public function setCriteriaCondition(null|string $criteriaCondition): void
    {
        $this->criteriaCondition = $criteriaCondition;
    }

    /**
     * Method to get the module api name of the custom view.
     *
     * @return string module api name
     */
    public function getModuleAPIName(): ?string
    {
        return $this->moduleAPIName;
    }

    /**
     * Method to get the module api name of the custom view.
     *
     * @param string $moduleapiname module api name
     */
    public function setModuleAPIName(null|string $moduleapiname): void
    {
        $this->moduleAPIName = $moduleapiname;
    }

    /**
     * Method to get the custom view records.
     *
     * @param array $param_map  key-value pair containing parameter names and the value
     * @param array $header_map key-value pair containing header names and the value
     *
     * @return BulkAPIResponse instance of the BulkAPIResponse class which holds the Bulk API response
     */
    public function getRecords(array $param_map = [], array $header_map = []): BulkAPIResponse
    {
        $param_map['cvid'] = $this->id;

        return ZCRMModule::getInstance($this->moduleAPIName)->getRecords($param_map, $header_map);
    }

    /**
     * method to get the categories List of the custom view.
     *
     * @return array array instances of the ZCRMCustomViewCategory
     */
    public function getCategoriesList(): array
    {
        return $this->categoriesList;
    }

    /**
     * Method to set the category list of the custom view.
     *
     * @param array $categoriesList array instances of ZCRMCustomViewCategory
     */
    public function setCategoriesList(array $categoriesList): void
    {
        $this->categoriesList = $categoriesList;
    }

    /**
     * Method to set the offline status of the custom view.
     *
     * @param bool $off_line true to set offline
     */
    public function setOffLine(null|bool $off_line): void
    {
        $this->offLine = $off_line;
    }

    /**
     * Method to check whether the custom view is offline or not.
     *
     * @return bool offline value (true if offline )
     */
    public function isOffLine(): ?bool
    {
        return $this->offLine;
    }
}
