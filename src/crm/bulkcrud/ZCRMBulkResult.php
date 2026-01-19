<?php

namespace zcrmsdk\crm\bulkcrud;

class ZCRMBulkResult
{
    /**
     * result page.
     */
    private ?int $page = null;

    /**
     * record count.
     */
    private ?int $count = null;

    /**
     * result download url.
     */
    private ?string $downloadUrl = null;

    /**
     * result per page.
     *
     * @var string
     */
    private ?int $perPage = null;

    /**
     * result more records.
     */
    private ?bool $moreRecords = null;

    /**
     * Method to get instance of ZCRMBulkResult class.
     *
     * @return ZCRMBulkResult - class instance
     */
    public static function getInstance(): ZCRMBulkResult
    {
        return new ZCRMBulkResult();
    }

    /**
     * Method to set the range of the number of records exported.
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * Method to get the range of the number of records exported.
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * Method to set the actual number of records exported.
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * Method to get the actual number of records exported.
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * Method to set the url which contains the CSV file.
     */
    public function setDownloadUrl(string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Method to get the url which contains the CSV file.
     */
    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    /**
     * Method to set the number of records in each page.
     */
    public function setPerPage(?int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * Method to get the number of records in each page.
     *
     * @return string
     */
    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    /**
     * Method to set the response can be used to detect if there are any further records.
     */
    public function setMoreRecords(?bool $moreRecords): void
    {
        $this->moreRecords = $moreRecords;
    }

    /**
     * Method to get the response can be used to detect if there are any further records.
     */
    public function getMoreRecords(): ?bool
    {
        return $this->moreRecords;
    }
}
