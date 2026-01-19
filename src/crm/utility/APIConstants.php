<?php

namespace zcrmsdk\crm\utility;

class APIConstants
{
    public const string ERROR = 'error';

    public const string REQUEST_METHOD_GET = 'GET';

    public const string REQUEST_METHOD_POST = 'POST';

    public const string REQUEST_METHOD_PUT = 'PUT';

    public const string REQUEST_METHOD_DELETE = 'DELETE';

    public const string OAUTH_HEADER_PREFIX = 'Zoho-oauthtoken ';

    public const string AUTHORIZATION = 'Authorization';

    public const string API_NAME = 'api_name';

    public const string INVALID_ID_MSG = 'The given id seems to be invalid.';

    public const string API_MAX_RECORDS_MSG = 'Cannot process more than 100 records at a time.';

    public const string API_MAX_ORGTAX_MSG = 'Cannot process more than 100 org taxes at a time.';

    public const string API_MAX_NOTES_MSG = 'Cannot process more than 100 notes at a time.';

    public const string API_MAX_TAGS_MSG = 'Cannot process more than 50 tags at a time.';

    public const string API_MAX_RECORD_TAGS_MSG = 'Cannot process more than 10 tags at a time.';

    public const string INVALID_DATA = 'INVALID_DATA';

    public const string CODE_SUCCESS = 'SUCCESS';

    public const string STATUS_SUCCESS = 'success';

    public const string STATUS_ERROR = 'error';

    public const string SDK_ERROR = 'ZCRM_INTERNAL_ERROR';

    public const string LEADS = 'Leads';

    public const string ACCOUNTS = 'Accounts';

    public const string CONTACTS = 'Contacts';

    public const string DEALS = 'Deals';

    public const string QUOTES = 'Quotes';

    public const string SALESORDERS = 'SalesOrders';

    public const string INVOICES = 'Invoices';

    public const string PURCHASEORDERS = 'PurchaseOrders';

    public const string PER_PAGE = 'per_page';

    public const string PAGE = 'page';

    public const string COUNT = 'count';

    public const string MORE_RECORDS = 'more_records';

    public const string ALLOWED_COUNT = 'allowed_count';

    public const string MESSAGE = 'message';

    public const string CODE = 'code';

    public const string STATUS = 'status';

    public const string DATA = 'data';

    public const string DETAILS = 'details';

    public const string MODULES = 'modules';

    public const string CUSTOM_VIEWS = 'custom_views';

    public const string TAGS = 'tags';

    public const string TAXES = 'taxes';

    public const string INFO = 'info';

    public const string ORG = 'org';

    public const string READ = 'read';

    public const string RESULT = 'result';

    public const string UPLOAD = 'upload';

    public const string WRITE = 'write';

    public const string CALLBACK = 'callback';

    public const string FILETYPE = 'file_type';

    public const string QUERY = 'query';

    public const string USERS = 'users';

    public const string HTTP_CODE = 'http_code';

    public const string VARIABLES = 'variables';
    public const int RESPONSECODE_OK = 200;

    public const int RESPONSECODE_CREATED = 201;

    public const int RESPONSECODE_ACCEPTED = 202;

    public const int RESPONSECODE_NO_CONTENT = 204;

    public const int RESPONSECODE_MOVED_PERMANENTLY = 301;

    public const int RESPONSECODE_MOVED_TEMPORARILY = 302;

    public const int RESPONSECODE_NOT_MODIFIED = 304;

    public const int RESPONSECODE_BAD_REQUEST = 400;

    public const int RESPONSECODE_AUTHORIZATION_ERROR = 401;

    public const int RESPONSECODE_FORBIDDEN = 403;

    public const int RESPONSECODE_NOT_FOUND = 404;

    public const int RESPONSECODE_METHOD_NOT_ALLOWED = 405;

    public const int RESPONSECODE_REQUEST_ENTITY_TOO_LARGE = 413;

    public const int RESPONSECODE_UNSUPPORTED_MEDIA_TYPE = 415;

    public const int RESPONSECODE_TOO_MANY_REQUEST = 429;

    public const int RESPONSECODE_INTERNAL_SERVER_ERROR = 500;

    public const string ACTION = 'action';

    public const string DUPLICATE_FIELD = 'duplicate_field';

    public const string ACCESS_TOKEN_EXPIRY = 'X-ACCESSTOKEN-RESET';

    public const string CURR_WINDOW_API_LIMIT = 'X-RATELIMIT-LIMIT';

    public const string CURR_WINDOW_REMAINING_API_COUNT = 'X-RATELIMIT-REMAINING';

    public const string CURR_WINDOW_RESET = 'X-RATELIMIT-RESET';

    public const string API_COUNT_REMAINING_FOR_THE_DAY = 'X-RATELIMIT-DAY-REMAINING';

    public const string API_LIMIT_FOR_THE_DAY = 'X-RATELIMIT-DAY-LIMIT';

    public const string APPLICATION_LOGFILE_PATH = 'applicationLogFilePath';

    public const string APPLICATION_LOGGER_INSTANCE = 'applicationLoggerInstance';

    public const string APPLICATION_LOG_RESPONSE_BODY = 'applicationLogResponseBody';

    public const string APPLICATION_LOG_RESPONSE_INFO = 'applicationLogResponseInfo';

    public const string APPLICATION_LOG_RESPONSE_HEADERS = 'applicationLogResponseHeaders';

    public const string APPLICATION_LOGFILE_NAME = '/ZCRMClientLibrary.log';

    public const string CURRENT_USER_EMAIL = 'currentUserEmail';

    public const string FILE_UPLOAD_URL = 'fileUploadUrl';

    public const string SANDBOX = 'sandbox';

    public const string API_BASE_URL = 'apiBaseUrl';

    public const string API_VERSION = 'apiVersion';

    public const string BULK_WRITE_STATUS = 'STATUS';

    public const array WRITE_STATUS = ['ADDED', 'UPDATED'];

    public const array INVENTORY_MODULES = ['Invoices', 'Sales_Orders', 'Purchase_Orders', 'Quotes'];
}
