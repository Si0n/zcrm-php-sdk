<?php

namespace zcrmsdk\crm\bulkapi\handler;

use zcrmsdk\crm\api\response\FileAPIResponse;
use zcrmsdk\crm\bulkapi\response\BulkResponse;
use zcrmsdk\crm\bulkcrud\ZCRMBulkRead;
use zcrmsdk\crm\bulkcrud\ZCRMBulkWrite;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\exception;
use zcrmsdk\crm\exception\APIExceptionHandler;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\utility\APIConstants;

class BulkAPIHandler
{
    protected ?ZCRMRecord $recordIns = null;
    protected ?string $fileName = null;

    private function __construct(
        protected ZCRMBulkRead $zcrmReadRecord,
        protected ZCRMBulkWrite $zcrmWriteRecord
    ) {
    }

    public static function getInstance(ZCRMBulkRead $zcrmReadRecord, ZCRMBulkWrite $zcrmWriteRecord): BulkAPIHandler
    {
        return new BulkAPIHandler($zcrmReadRecord, $zcrmWriteRecord);
    }

    public function processZip(string $filePath, bool $download, ?string $fileName, string $operationType, ?string $fileURL, ?bool $checkFailed = false): BulkResponse
    {
        $fieldAPINames = [];
        $fileType = null;
        $csvFilePointer = null;
        $eventsData = [];
        try {
            if (APIConstants::READ == $operationType) {
                if ($download && null == $fileName) {
                    $fileResponse = BulkReadAPIHandler::getInstance($this->zcrmReadRecord)->downloadBulkReadResult();
                    if (200 != $fileResponse->getHttpStatusCode()) {
                        throw new ZCRMException('zip file not downloaded');
                    }  
                        if (!self::writeStreamtoZipFile($fileResponse, $filePath . '/')) {
                            throw new ZCRMException('Error while writing file in the file path specified: ' . $filePath . '/', APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (!self::unzip($filePath . '/' . $fileResponse->getFileName(), $filePath . '/')) {
                            throw new ZCRMException('Error occurred while unzipping the file: ' . $filePath . '/' . $fileResponse->getFileName(), APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (!($csvFilePointer = fopen($filePath . '/' . $this->fileName, 'r'))) {
                            throw new ZCRMException(sprintf('csv-reader: Could not read CSV "%s" / %s', $filePath, $this->fileName), APIConstants::RESPONSECODE_BAD_REQUEST);
                        }

                    $fileResponse = null;
                }
                $moduleAPIName = $this->zcrmReadRecord->getModuleAPIName();
            } else {
                if ($download && null == $fileName && null !== $fileURL) {
                    $fileResponse = BulkWriteAPIHandler::getInstance($this->zcrmWriteRecord)->downloadBulkWriteResult($fileURL);
                    if (200 != $fileResponse->getHttpStatusCode()) {
                        throw new ZCRMException('zip file not downloaded', $fileResponse->getHttpStatusCode());
                    }  
                        if (!self::writeStreamtoZipFile($fileResponse, $filePath . '/')) {
                            throw new ZCRMException('Error while writing file in the file path specified: ' . $filePath . '/', APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (!self::unzip($filePath . '/' . $fileResponse->getFileName(), $filePath . '/')) {
                            throw new ZCRMException('Error occurred while unzipping the file: ' . $filePath . '/' . $fileResponse->getFileName(), APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (!($csvFilePointer = fopen($filePath . '/' . $this->fileName, 'r'))) {
                            throw new ZCRMException(sprintf('csv-reader: Could not read CSV "%s" / %s', $filePath, $this->fileName), APIConstants::RESPONSECODE_BAD_REQUEST);
                        }

                    $fileResponse = null;
                }
                $moduleAPIName = $this->zcrmWriteRecord->getModuleAPIName();
            }
            if (null == $csvFilePointer) {
                if (!self::unzip($filePath . '/' . $fileName, $filePath . '/')) {
                    throw new ZCRMException('Error occurred while unzipping the file: ' . $filePath . '/' . $fileName, APIConstants::RESPONSECODE_BAD_REQUEST);
                }
                if (!($csvFilePointer = fopen($filePath . '/' . $this->fileName, 'r'))) {
                    throw new ZCRMException(sprintf('csv-reader: Could not read CSV "%s" / %s', $filePath, $this->fileName), APIConstants::RESPONSECODE_BAD_REQUEST);
                }
            }
            if (strpos($this->fileName, '.ics')) {
                $fileType = 'ics';
                if (!($value = fgetcsv($csvFilePointer))) {
                    throw new ZCRMException('The file is empty', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
                $len = 0;
                do {
                    $value_arr = explode(':', $value[0], 2);
                    $len += strlen($value[0]);
                    if (!array_key_exists($value_arr[0], $eventsData)) {
                        $eventsData[$value_arr[0]] = $value_arr[1];
                    } else {
                        fseek($csvFilePointer, $len);
                        break;
                    }
                } while (($value = fgetcsv($csvFilePointer)) !== false);
            } elseif (!($fieldAPINames = fgetcsv($csvFilePointer))) {
                throw new ZCRMException('The file is empty', APIConstants::RESPONSECODE_BAD_REQUEST);
            }
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);
            throw $exception;
        }
        $this->fileName = null;
        $bulkResponse = new BulkResponse($moduleAPIName, $csvFilePointer, $checkFailed, $fileType);
        $bulkResponse->setFieldNames($fieldAPINames);
        $bulkResponse->setEntityAPIHandlerIns($this);
        if ('ics' == $fileType) {
            $eventsData['EventsData'] = $bulkResponse;
            $eventsData['END'] = $eventsData['BEGIN'];
            $bulkResponse->setData($eventsData);
        }

        return $bulkResponse;
    }

    private function writeStreamtoZipFile(FileAPIResponse $fileResponse, $filePath)
    {
        try {
            try {
                $filePointer = fopen($filePath . $fileResponse->getFileName(), 'w'); // $filePath - absolute path where downloaded file has to be stored.
                $stream = $fileResponse->getFileContent();
                fputs($filePointer, $stream);
                fclose($filePointer);
            } catch (exception $ex) {
                throw new ZCRMException($ex);
            }
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);

            return false;
        }

        return true;
    }

    public function setRecordProperties(string $moduleAPIName, array $fieldVsValues, int $rowNumber): ZCRMRecord
    {
        $this->recordIns = ZCRMRecord::getInstance($moduleAPIName, null);
        $this->recordIns->setRecordRowNumber($rowNumber);
        foreach ($fieldVsValues as $key => $value) {
            if (('Id' == $key || 'RECORD_ID' == $key) && null !== $value) {
                $this->recordIns->setEntityId($value);
            } elseif ('Created_By' == $key && null !== $value) {
                $createdBy = null !== $this->recordIns->getCreatedBy() ? $this->recordIns->getCreatedBy() : ZCRMUser::getInstance();
                $createdBy->setId($value);
                $this->recordIns->setCreatedBy($createdBy);
            } elseif ('Modified_By' == $key && null !== $value) {
                $modifiedBy = null !== $this->recordIns->getModifiedBy() ? $this->recordIns->getModifiedBy() : ZCRMUser::getInstance();
                $modifiedBy->setId($value);
                $this->recordIns->setModifiedBy($modifiedBy);
            } elseif ('Created_Time' == $key && null !== $value) {
                $this->recordIns->setCreatedTime('' . $value);
            } elseif ('Modified_Time' == $key && null !== $value) {
                $this->recordIns->setModifiedTime('' . $value);
            } elseif ('Owner' == $key && null !== $value) {
                $owner = null !== $this->recordIns->getOwner() ? $this->recordIns->getModifiedBy() : ZCRMUser::getInstance();
                $owner->setId($value);
                $this->recordIns->setOwner($owner);
            } elseif (str_starts_with($key, '$') && null !== $value) {
                $this->recordIns->setProperty(str_replace('$', '', $key), $value);
            } elseif ('STATUS' == $key && null !== $value) {
                $this->recordIns->setStatus($value);
            } elseif ('ERRORS' == $key && null !== $value) {
                $this->recordIns->setErrorMessage($value);
            } elseif (null !== $value && '0' !== $value) {
                $this->recordIns->setFieldValue($key, $value);
            }
        }

        return $this->recordIns;
    }

    private function unzip(string $zipFilePath, string $destDir): bool
    {
        try {
            if (!file_exists($zipFilePath . '.zip') && !file_exists($zipFilePath) && !file_exists($zipFilePath . '.csv')) {
                return false;
            }

            $zipFilePath = file_exists($zipFilePath . '.zip') ? $zipFilePath . '.zip' : $zipFilePath;
            if (!str_contains($zipFilePath, 'zip')) {
                $this->fileName = file_exists($zipFilePath . '.csv') ? basename($zipFilePath . '.csv') : basename($zipFilePath);

                return true;
            }
            $zip = new \ZipArchive();
            if (true !== $zip->open($zipFilePath)) {
                return false;
            }
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $stat = $zip->statIndex($i);
                $this->fileName = trim(basename($stat['name']) . PHP_EOL);
            }
            $zip->extractTo($destDir);
            $zip->close();
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    public function next(string $moduleAPIName, ?array $fieldVsValues, int $rowNumber): ZCRMRecord
    {
        if (null !== $fieldVsValues && sizeof($fieldVsValues) > 0) {
            return self::setRecordProperties($moduleAPIName, $fieldVsValues, $rowNumber);
        }

        return ZCRMRecord::getInstance(null, null);
    }

    public function __destruct()
    {
        unset($this->record);
        unset($this->recordIns);
    }
}
