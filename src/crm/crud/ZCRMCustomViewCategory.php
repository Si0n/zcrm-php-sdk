<?php

namespace zcrmsdk\crm\crud;

class ZCRMCustomViewCategory
{
    /**
     * display value of category.
     */
    private $displayValue;

    /**
     * actual value of the criteria.
     *
     * @var string
     */
    private $actualValue;

    private function __construct()
    {
    }

    /**
     * Method to create an instance of ZCRMCustomViewCategory.
     *
     * @return ZCRMCustomViewCategory instance of ZCRMCustomViewCategory
     */
    public static function getInstance()
    {
        return new ZCRMCustomViewCategory();
    }

    /**
     * Method to get the displayValue of the custom view category.
     *
     * @return string the display value
     */
    public function getDisplayValue()
    {
        return $this->displayValue;
    }

    /**
     * Method to set the displayValue for the custom view category.
     *
     * @param string $displayValue the display value
     */
    public function setDisplayValue($displayValue)
    {
        $this->displayValue = $displayValue;
    }

    /**
     * Method to get the actual Value of the custom view category.
     *
     * @return string the actual value
     */
    public function getActualValue()
    {
        return $this->actualValue;
    }

    /**
     * Method set the actual Value for the custom view category.
     *
     * @param string $actualValue the actual value
     */
    public function setActualValue($actualValue)
    {
        $this->actualValue = $actualValue;
    }
}
