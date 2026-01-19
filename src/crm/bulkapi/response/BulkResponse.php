<?php

namespace zcrmsdk\crm\bulkapi\response;

use zcrmsdk\crm\bulkapi\handler\BulkAPIHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\utility\APIConstants;

class BulkResponse
{
    private array $fieldAPINames = [];
    private array $fieldsvsValue = [];
    private ?BulkAPIHandler $apiHandlerIns = null;
    private int $rowNumber = 0;
    private ?array $data = [];

    public function __construct(
        protected string $moduleAPIName,
        protected mixed $filePointer,
        protected ?bool $checkFailedRecord,
        protected ?string $fileType
    ) {
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function setFieldValues(array $fieldValues): void
    {
        if (sizeof($fieldValues) !== sizeof($this->fieldAPINames)) {
            return;
        }

        foreach ($this->fieldAPINames as $index => $fieldName) {
            $this->fieldsvsValue[$fieldName] = $fieldValues[$index];
        }
    }

    public function setModuleAPIName(string $moduleAPIName): void
    {
        $this->moduleAPIName = $moduleAPIName;
    }

    public function getModuleAPIName(): string
    {
        return $this->moduleAPIName;
    }

    public function setFieldNames(array $fieldAPINames): void
    {
        $this->fieldAPINames = $fieldAPINames;
    }

    public function getFieldNames(): array
    {
        return $this->fieldAPINames;
    }

    public function setEntityAPIHandlerIns(?BulkAPIHandler $apiHandlerIns): void
    {
        $this->apiHandlerIns = $apiHandlerIns;
    }

    public function getEntityAPIHandlerIns(): ?BulkAPIHandler
    {
        return $this->apiHandlerIns;
    }

    public function next()
    {
        return $this->apiHandlerIns->next($this->moduleAPIName, $this->fieldsvsValue, $this->rowNumber);
    }

    public function hasNext()
    {
        $this->fieldsvsValue = [];
        try {
            if (!is_resource($this->filePointer)) {
                return false;
            }
            if (($fieldValues = fgetcsv($this->filePointer)) != false) {
                if ('ics' == $this->fileType) {
                    do {
                        if (strpos($fieldValues[0], ':')) {
                            $value = explode(':', $fieldValues[0], 2);
                            if ('END' == $value[0] && count($this->fieldsvsValue) > 0) {
                                $this->fieldsvsValue[$value[0]] = $value[1];

                                return true;
                            }  
                                $this->fieldsvsValue[$value[0]] = $value[1];
                        }
                    } while (($fieldValues = fgetcsv($this->filePointer)) !== false);
                    fclose($this->filePointer);
                } elseif ($this->checkFailedRecord) {
                    do {
                        ++$this->rowNumber;
                        if (in_array(APIConstants::BULK_WRITE_STATUS, $this->fieldAPINames)) {
                            $index = array_search(APIConstants::BULK_WRITE_STATUS, $this->fieldAPINames);
                            if (!in_array($fieldValues[$index], APIConstants::WRITE_STATUS)) {
                                self::setFieldValues($fieldValues);

                                return true;
                            }
                        }
                    } while (($fieldValues = fgetcsv($this->filePointer)) !== false);
                    $this->rowNumber = 0;
                    fclose($this->filePointer);
                } else {
                    if (null != $fieldValues) {
                        self::setFieldValues($fieldValues);
                        ++$this->rowNumber;

                        return true;
                    }  
                        $this->rowNumber = 0;
                        fclose($this->filePointer);
                }
            }

            return false;
        } catch (\Exception $ex) {
            throw new ZCRMException($ex, APIConstants::RESPONSECODE_BAD_REQUEST);
        }
    }

    public function close()
    {
        $this->rowNumber = 0;
        fclose($this->filePointer);
    }

    public function __destruct()
    {
        $this->moduleAPIName = null;
        $this->fieldAPINames = null;
        $this->fieldValues = null;
        unset($this->apiHandlerIns);
    }
}
