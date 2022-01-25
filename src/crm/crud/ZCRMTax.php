<?php

namespace zcrmsdk\crm\crud;

/**
 * @author sumanth-3058
 *         This class is to maintain the details of tax related to Inventory line item
 */
class ZCRMTax
{
    /**
     * tax name.
     *
     * @var string
     */
    private $taxName = null;

    /**
     * percentage of the tax.
     *
     * @var float
     */
    private $percentage = null;

    /**
     * tax value.
     *
     * @var float
     */
    private $value = null;

    /**
     * constructor to assign tax name.
     *
     * @param string $taxName tax name
     */
    private function __construct($taxName)
    {
        $this->taxName = $taxName;
    }

    /**
     * method to get the instance of the tax.
     *
     * @param string $taxName tax name
     *
     * @return ZCRMTax ZCRMTax class instance
     */
    public static function getInstance($taxName)
    {
        return new ZCRMTax($taxName);
    }

    /**
     * method to get the tax name.
     *
     * @return string the tax name
     */
    public function getTaxName()
    {
        return $this->taxName;
    }

    /**
     * method to set the tax name.
     *
     * @param string $taxName the tax name
     */
    public function setTaxName($taxName)
    {
        $this->taxName = $taxName;
    }

    /**
     * method to get the tax percentage.
     *
     * @return float the tax percentage
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * method to set the tax percentage.
     *
     * @param float $percentage the tax percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * method to get the tax value.
     *
     * @return float the tax value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * method to set the tax value.
     *
     * @param float $value the tax value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
