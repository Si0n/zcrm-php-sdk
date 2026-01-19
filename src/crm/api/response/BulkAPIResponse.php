<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;

class BulkAPIResponse extends CommonAPIResponse
{
    private mixed $bulkData = null;

    private ?string $status = null;

    private ?ResponseInfo $info = null;

    /** @var EntityResponse[] */
    private array $bulkEntitiesResponse = [];

    public function __construct(?string $httpResponse, ?int $httpStatusCode)
    {
        parent::__construct($httpResponse, $httpStatusCode);
        $this->setInfo();
    }

    /**
     * @throws ZCRMException
     */
    public function handleForFaultyResponses(): void
    {
        $statusCode = self::getHttpStatusCode();
        if (in_array($statusCode, APIExceptionHandler::getFaultyResponseCodes())) {
            if (APIConstants::RESPONSECODE_NO_CONTENT === $statusCode) {
                $exception = new ZCRMException('No Content', $statusCode);
                $exception->setExceptionCode('NO CONTENT');
                throw $exception;
            }
            if (APIConstants::RESPONSECODE_NOT_MODIFIED === $statusCode) {
                $exception = new ZCRMException('Not Modified', $statusCode);
                $exception->setExceptionCode('NOT MODIFIED');
                throw $exception;
            }
            $responseJSON = $this->getResponseJSON();
            $exception = new ZCRMException($responseJSON[APIConstants::MESSAGE] ?? 'Unknown', $statusCode);
            $exception->setExceptionCode($responseJSON[APIConstants::CODE] ?? 'Unknown');
            $exception->setExceptionDetails($responseJSON[APIConstants::DETAILS] ?? []);
            throw $exception;
        }
    }

    public function processResponseData(): void
    {
        $this->bulkEntitiesResponse = [];
        $bulkResponseJSON = $this->getResponseJSON();
        foreach ([APIConstants::DATA, APIConstants::TAGS, APIConstants::TAXES, APIConstants::VARIABLES] as $key) {
            if (array_key_exists($key, $bulkResponseJSON)) {
                $recordsArray = $bulkResponseJSON[$key];
                foreach ($recordsArray as $record) {
                    if (null !== $record && array_key_exists(APIConstants::STATUS, $record)) {
                        $this->bulkEntitiesResponse[] = new EntityResponse($record);
                    }
                }
            }
        }
    }

    public function getData(): mixed
    {
        return $this->bulkData;
    }

    public function setData(mixed $bulkData): void
    {
        $this->bulkData = $bulkData;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getInfo(): ?ResponseInfo
    {
        return $this->info;
    }

    public function setInfo(): void
    {
        if (array_key_exists(APIConstants::INFO, $this->getResponseJSON())) {
            $this->info = new ResponseInfo($this->getResponseJSON()[APIConstants::INFO]);
        }
    }

    /**
     * @return EntityResponse[]
     */
    public function getEntityResponses(): array
    {
        return $this->bulkEntitiesResponse;
    }
}
