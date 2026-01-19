<?php

namespace zcrmsdk\crm\api\response;

use zcrmsdk\crm\utility\APIConstants;

class EntityResponse
{
    private ?string $status;
    private ?string $message;
    private ?string $code;
    private array $responseJSON;
    private mixed $data;
    private array $upsertDetails = [];
    private array $details;

    public function __construct(array $entityResponseJSON)
    {
        $this->responseJSON = $entityResponseJSON;
        $this->status = $entityResponseJSON[APIConstants::STATUS] ?? null;
        $this->message = $entityResponseJSON[APIConstants::MESSAGE] ?? null;
        $this->code = $entityResponseJSON[APIConstants::CODE] ?? null;
        if (array_key_exists(APIConstants::ACTION, $entityResponseJSON)) {
            $this->upsertDetails[APIConstants::ACTION] = $entityResponseJSON[APIConstants::ACTION];
        }
        if (array_key_exists(APIConstants::DUPLICATE_FIELD, $entityResponseJSON)) {
            $this->upsertDetails[APIConstants::DUPLICATE_FIELD] = $entityResponseJSON[APIConstants::DUPLICATE_FIELD];
        }
        if (array_key_exists('details', $entityResponseJSON)) {
            $this->details = $entityResponseJSON['details'];
        }
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getResponseJSON(): array
    {
        return $this->responseJSON;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

    public function getUpsertDetails(): array
    {
        return $this->upsertDetails;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
