<?php

namespace zcrmsdk\crm\crud;

/**
 * This class is to maintain the details of inventory line item.
 *
 * @author sumanth-3058
 */
class ZCRMInventoryLineItem
{
    /**
     * inventory line item id.
     *
     * @var string
     */
    private $id;

    /**
     * product record.
     *
     * @var ZCRMRecord
     */
    private $product;

    /**
     * price of the product.
     *
     * @var float
     */
    private $listPrice;

    /**
     * quantity of the product.
     *
     * @var int
     */
    private $quantity;

    /**
     * description of the product.
     *
     * @var string
     */
    private $description;

    /**
     * total price of the product.
     *
     * @var float
     */
    private $total;

    /**
     * discount on the product.
     *
     * @var float
     */
    private $discount;

    /**
     * percentage of discount.
     *
     * @var int
     */
    private $discountPercentage;

    /**
     * total price after the discount.
     *
     * @var float
     */
    private $totalAfterDiscount;

    /**
     * tax on the product.
     *
     * @var float
     */
    private $taxAmount;

    /**
     * net total of the product.
     *
     * @var float
     */
    private $netTotal;

    /**
     *array of line taxes.
     *
     * @var string
     */
    private $lineTax = [];

    /**
     * constructor to set the ZCRMRecord product record to the inventory line item.
     *
     * @param ZCRMRecord $param instance of the ZCRMRecord product
     */
    private function __construct($param)
    {
        if ($param instanceof ZCRMRecord) {
            $this->product = $param;
        } else {
            $this->id = $param;
        }
    }

    /**
     * Method to get ZCRMInventoryLineItem instance.
     *
     * @param ZCRMRecord $param instance of ZCRMRecord product instance
     *
     * @return ZCRMInventoryLineItem instance of ZCRMInventoryLineItem class
     */
    public static function getInstance($param)
    {
        return new ZCRMInventoryLineItem($param);
    }

    /**
     * Method to get the inventory line item id.
     *
     * @return string inventory line item id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Method to set the inventory line item id.
     *
     * @param string $id the inventory line item id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * method to get the inventory line item product.
     *
     * @return ZCRMRecord instance of the ZCRMRecord class
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * method to make the product as a inventory line item.
     *
     * @param ZCRMRecord $product instance of the ZCRMRecord class
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * Method to get the list Price of the inventory line item.
     *
     * @return float list Price of the inventory line item
     */
    public function getListPrice()
    {
        return $this->listPrice;
    }

    /**
     * Method to set the list Price of the inventory line item.
     *
     * @param float $listPrice list Price of the inventory line item
     */
    public function setListPrice($listPrice)
    {
        $this->listPrice = $listPrice;
    }

    /**
     * Method to get the quantity of the inventory line item.
     *
     * @return float quantity of the inventory line item
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Method to set the quantity of the inventory line item.
     *
     * @param float $quantity quantity of the inventory line item
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Method to get the description of the inventory line item.
     *
     * @return string description of the inventory line item
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Method to set the description of the inventory line item.
     *
     * @param string $description description of the inventory line item
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Method to get the total price of the inventory line item.
     *
     * @return float total price of the inventory line item
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Method to set the total price of the inventory line item.
     *
     * @param float $total total price of the inventory line item
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * Method to get the discount of the inventory line item.
     *
     * @return float discount of the inventory line item
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Method to set the discount of the inventory line item.
     *
     * @param float $discount discount of the inventory line item
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * Method to get the discount Percentage of the inventory line item.
     *
     * @return float discount Percentage of the inventory line item
     */
    public function getDiscountPercentage()
    {
        return $this->discountPercentage;
    }

    /**
     * Method to set the discount Percentage of the inventory line item.
     *
     * @param float $discountPercentage discount Percentage of the inventory line item
     */
    public function setDiscountPercentage($discountPercentage)
    {
        $this->discountPercentage = $discountPercentage;
    }

    /**
     * Method to get the total After Discount price of the inventory line item.
     *
     * @return float total After Discount price of the inventory line item
     */
    public function getTotalAfterDiscount()
    {
        return $this->totalAfterDiscount;
    }

    /**
     * Method to set the total After Discount price of the inventory line item.
     *
     * @param float $totalAfterDiscount total After Discount price of the inventory line item
     */
    public function setTotalAfterDiscount($totalAfterDiscount)
    {
        $this->totalAfterDiscount = $totalAfterDiscount;
    }

    /**
     * Method to get the tax amount of the inventory line item.
     *
     * @return float tax amount of the inventory line item
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * Method to set the tax amount of the inventory line item.
     *
     * @param float $taxAmount tax amount of the inventory line item
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
    }

    /**
     * Method to get the net total price of the inventory line item.
     *
     * @return float total price of the inventory line item
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * Method to set the net total price of the inventory line item.
     *
     * @param float $netTotal total price of the inventory line item
     */
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;
    }

    /**
     * Method to get the tax to the inventory line item.
     *
     * @return array ZCRMTax array of ZCRMTax instances related to the inventory line item
     */
    public function getLineTax()
    {
        return $this->lineTax;
    }

    /**
     * Method to add a tax to the inventory line item.
     *
     * @param array $lineTax array of ZCRMTax class instances
     */
    public function addLineTax($lineTax)
    {
        array_push($this->lineTax, $lineTax);
    }
}
