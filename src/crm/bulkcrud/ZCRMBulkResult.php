<?php

namespace zcrmsdk\crm\bulkcrud;

class ZCRMBulkResult
{
    /**
     * result page.
     *
     * @var int
     */
    private $page;

    /**
     * record count.
     *
     * @var int
     */
    private $count;

    /**
     * result download url.
     *
     * @var string
     */
    private $downloadUrl;

    /**
     * result per page.
     *
     * @var string
     */
    private $perPage;

    /**
     * result more records.
     *
     * @var bool
     */
    private $moreRecords;

    /**
     * Method to get instance of ZCRMBulkResult class.
     *
     * @return ZCRMBulkResult - class instance
     */
    public static function getInstance()
    {
        return new ZCRMBulkResult();
    }

    /**
     * Method to set the range of the number of records exported.
     *
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * Method to get the range of the number of records exported.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Method to set the actual number of records exported.
     *
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * Method to get the actual number of records exported.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Method to set the url which contains the CSV file.
     *
     * @param string $downloadUrl
     */
    public function setDownloadUrl($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Method to get the url which contains the CSV file.
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * Method to set the number of records in each page.
     *
     * @param int $perPage
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }

    /**
     * Method to get the number of records in each page.
     *
     * @return string
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Method to set the response can be used to detect if there are any further records.
     *
     * @param bool $moreRecords
     */
    public function setMoreRecords($moreRecords)
    {
        $this->moreRecords = $moreRecords;
    }

    /**
     * Method to get the response can be used to detect if there are any further records.
     *
     * @return bool
     */
    public function getMoreRecords()
    {
        return $this->moreRecords;
    }
}
