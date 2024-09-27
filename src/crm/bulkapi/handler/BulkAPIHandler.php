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
    protected $bulkReadRecordIns;
    protected $bulkWriteRecordIns;
    protected $recordIns;
    protected $fileName;

    private function __construct(ZCRMBulkRead $zcrmReadRecord, ZCRMBulkWrite $zcrmWriteRecord)
    {
        $this->bulkReadRecordIns = $zcrmReadRecord;
        $this->bulkWriteRecordIns = $zcrmWriteRecord;
    }

    public static function getInstance(ZCRMBulkRead $zcrmReadRecord, ZCRMBulkWrite $zcrmWriteRecord)
    {
        return new BulkAPIHandler($zcrmReadRecord, $zcrmWriteRecord);
    }

    public function processZip($filePath, $download, $fileName, $operationType, $fileURL, $checkFailed)
    {
        $fieldAPINames = [];
        $fileType = null;
        $csvFilePointer = null;
        $moduleAPIName = null;
        $eventsData = [];
        try {
            if (APIConstants::READ == $operationType) {
                if ($download && null == $fileName) {
                    $fileResponse = BulkReadAPIHandler::getInstance($this->bulkReadRecordIns)->downloadBulkReadResult();
                    if (200 != $fileResponse->getHttpStatusCode()) {
                        throw new ZCRMException('zip file not downloaded');
                    } else {
                        if (!self::writeStreamtoZipFile($fileResponse, $filePath . '/')) {
                            throw new ZCRMException('Error while writing file in the file path specified: ' . $filePath . '/', APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (!self::unzip($filePath . '/' . $fileResponse->getFileName(), $filePath . '/')) {
                            throw new ZCRMException('Error occurred while unzipping the file: ' . $filePath . '/' . $fileResponse->getFileName(), APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (($csvFilePointer = fopen($filePath . '/' . $this->fileName, 'r')) == false) {
                            throw new ZCRMException("csvreader: Could not read CSV \"$filePath . " / " . $this->fileName . \"", APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                    }
                    $fileResponse = null;
                }
                $moduleAPIName = $this->bulkReadRecordIns->getModuleAPIName();
            } else {
                if ($download && null == $fileName && null != $fileURL) {
                    $fileResponse = BulkWriteAPIHandler::getInstance($this->bulkWriteRecordIns)->downloadBulkWriteResult($fileURL);
                    if (200 != $fileResponse->getHttpStatusCode()) {
                        throw new ZCRMException('zip file not downloaded', $fileResponse->getHttpStatusCode());
                    } else {
                        if (!self::writeStreamtoZipFile($fileResponse, $filePath . '/')) {
                            throw new ZCRMException('Error while writing file in the file path specified: ' . $filePath . '/', APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (!self::unzip($filePath . '/' . $fileResponse->getFileName(), $filePath . '/')) {
                            throw new ZCRMException('Error occurred while unzipping the file: ' . $filePath . '/' . $fileResponse->getFileName(), APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                        if (($csvFilePointer = fopen($filePath . '/' . $this->fileName, 'r')) == false) {
                            throw new ZCRMException("csvreader: Could not read \".$filePath." / ".$this->fileName.\"", APIConstants::RESPONSECODE_BAD_REQUEST);
                        }
                    }
                    $fileResponse = null;
                }
                $moduleAPIName = $this->bulkWriteRecordIns->getModuleAPIName();
            }
            if (null == $csvFilePointer) {
                if (!self::unzip($filePath . '/' . $fileName, $filePath . '/')) {
                    throw new ZCRMException('Error occurred while unzipping the file: ' . $filePath . '/' . $fileName, APIConstants::RESPONSECODE_BAD_REQUEST);
                }
                if (($csvFilePointer = fopen($filePath . '/' . $this->fileName, 'r')) == false) {
                    throw new ZCRMException("csvreader: Could not read \".$filePath . " / " . $this->fileName.\"", APIConstants::RESPONSECODE_BAD_REQUEST);
                }
            }
            if (strpos($this->fileName, '.ics')) {
                $fileType = 'ics';
                if (($value = fgetcsv($csvFilePointer)) == false) {
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
            } else {
                if (($fieldAPINames = fgetcsv($csvFilePointer)) == false) {
                    throw new ZCRMException('The file is empty', APIConstants::RESPONSECODE_BAD_REQUEST);
                }
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
            } catch (Exception $ex) {
                throw new ZCRMException($ex);
            }
        } catch (ZCRMException $exception) {
            APIExceptionHandler::logException($exception);

            return false;
        }

        return true;
    }

    public function setRecordProperties($moduleAPIName, $fieldvsValues, $rowNumber)
    {
        unset($this->recordIns);
        $this->recordIns = ZCRMRecord::getInstance($moduleAPIName, null);
        $this->recordIns->setRecordRowNumber($rowNumber);
        foreach ($fieldvsValues as $key => $value) {
            if (('Id' == $key || 'RECORD_ID' == $key) && null != $value) {
                $this->recordIns->setEntityId($value);
            } elseif ('Created_By' == $key && null != $value) {
                $createdBy = null != $this->recordIns->getCreatedBy() ? $this->recordIns->getCreatedBy() : ZCRMUser::getInstance();
                $createdBy->setId($value);
                $this->recordIns->setCreatedBy($createdBy);
            } elseif ('Modified_By' == $key && null != $value) {
                $modifiedBy = null != $this->recordIns->getModifiedBy() ? $this->recordIns->getModifiedBy() : ZCRMUser::getInstance();
                $modifiedBy->setId($value);
                $this->recordIns->setModifiedBy($modifiedBy);
            } elseif ('Created_Time' == $key && null != $value) {
                $this->recordIns->setCreatedTime('' . $value);
            } elseif ('Modified_Time' == $key && null != $value) {
                $this->recordIns->setModifiedTime('' . $value);
            } elseif ('Owner' == $key && null != $value) {
                $owner = null != $this->recordIns->getOwner() ? $this->recordIns->getModifiedBy() : ZCRMUser::getInstance();
                $owner->setId($value);
                $this->recordIns->setOwner($owner);
            } elseif ('$' == substr($key, 0, 1) && null != $value) {
                $this->recordIns->setProperty(str_replace('$', '', $key), $value);
            } elseif ('STATUS' == $key && null != $value) {
                $this->recordIns->setStatus($value);
            } elseif ('ERRORS' == $key && null != $value) {
                $this->recordIns->setErrorMessage($value);
            } else {
                if (null != $value && '0' != $value) {
                    $this->recordIns->setFieldValue($key, $value);
                }
            }
        }

        return $this->recordIns;
    }

    private function unzip($zipFilePath, $destDir)
    {
        try {
            if (file_exists($zipFilePath . '.zip') || file_exists($zipFilePath) || file_exists($zipFilePath . '.csv')) {
                $zipFilePath = file_exists($zipFilePath . '.zip') ? $zipFilePath . '.zip' : $zipFilePath;
                if (false !== strpos($zipFilePath, 'zip')) {
                    $zip = new \ZipArchive();
                    if (true === $zip->open($zipFilePath)) {
                        for ($i = 0; $i < $zip->numFiles; ++$i) {
                            $stat = $zip->statIndex($i);
                            $this->fileName = trim(basename($stat['name']) . PHP_EOL);
                        }
                        $zip->extractTo($destDir);
                        $zip->close();
                    } else {
                        return false;
                    }
                } else {
                    $this->fileName = file_exists($zipFilePath . '.csv') ? basename($zipFilePath . '.csv') : basename($zipFilePath);
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function next($moduleAPIName, $fieldvsValues, $rowNumber)
    {
        if (null != $fieldvsValues && sizeof($fieldvsValues) > 0) {
            return self::setRecordProperties($moduleAPIName, $fieldvsValues, $rowNumber);
        } else {
            return ZCRMRecord::getInstance(null, null);
        }
    }

    public function __destruct()
    {
        unset($this->record);
        unset($this->recordIns);
    }
}
