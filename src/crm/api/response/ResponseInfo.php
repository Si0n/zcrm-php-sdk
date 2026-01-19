<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\utility\APIConstants;

class ResponseInfo
{
    protected ?bool $moreRecords = null;
    protected ?int $recordCount = null;
    protected ?int $pageNo = null;
    protected ?int $perPage = null;
    protected ?int $allowedCount = null;

    public function __construct(array $responseInfoJSON)
    {
        if (array_key_exists(APIConstants::MORE_RECORDS, $responseInfoJSON)) {
            $this->moreRecords = (bool) $responseInfoJSON[APIConstants::MORE_RECORDS];
        }
        if (array_key_exists(APIConstants::COUNT, $responseInfoJSON)) {
            $this->recordCount = (int) $responseInfoJSON[APIConstants::COUNT];
        }
        if (array_key_exists(APIConstants::PAGE, $responseInfoJSON)) {
            $this->pageNo = (int) $responseInfoJSON[APIConstants::PAGE];
        }
        if (array_key_exists(APIConstants::PER_PAGE, $responseInfoJSON)) {
            $this->perPage = (int) $responseInfoJSON[APIConstants::PER_PAGE];
        }
        if (array_key_exists(APIConstants::ALLOWED_COUNT, $responseInfoJSON)) {
            $this->allowedCount = (int) $responseInfoJSON[APIConstants::ALLOWED_COUNT];
        }
    }

    public function getMoreRecords(): ?bool
    {
        return $this->moreRecords;
    }

    public function setMoreRecords(?bool $moreRecords): void
    {
        $this->moreRecords = $moreRecords;
    }

    public function getRecordCount(): ?int
    {
        return $this->recordCount;
    }

    public function setRecordCount(?int $recordCount): void
    {
        $this->recordCount = $recordCount;
    }

    public function getPageNo(): ?int
    {
        return $this->pageNo;
    }

    public function setPageNo(?int $pageNo): void
    {
        $this->pageNo = $pageNo;
    }

    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    public function setPerPage(?int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getAllowedCount(): ?int
    {
        return $this->allowedCount;
    }

    public function setAllowedCount(?int $allowedCount): void
    {
        $this->allowedCount = $allowedCount;
    }
}
