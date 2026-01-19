<?php

namespace zcrmsdk\crm\api\handler;

interface APIHandlerInterface
{
    public function getRequestMethod(): ?string;

    public function getUrlPath(): ?string;

    public function getRequestBody(): mixed;

    public function getRequestHeaders(): ?array;

    public function getRequestParams(): ?array;

    public function getRequestHeadersAsMap(): ?array;

    public function getRequestParamsAsMap(): ?array;
}
