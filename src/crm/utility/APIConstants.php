<?php

namespace zcrmsdk\crm\utility;

class APIConstants
{
    public const ERROR = 'error';

    public const REQUEST_METHOD_GET = 'GET';

    public const REQUEST_METHOD_POST = 'POST';

    public const REQUEST_METHOD_PUT = 'PUT';

    public const REQUEST_METHOD_DELETE = 'DELETE';

    public const OAUTH_HEADER_PREFIX = 'Zoho-oauthtoken ';

    public const AUTHORIZATION = 'Authorization';

    public const API_NAME = 'api_name';

    public const INVALID_ID_MSG = 'The given id seems to be invalid.';

    public const API_MAX_RECORDS_MSG = 'Cannot process more than 100 records at a time.';

    public const API_MAX_ORGTAX_MSG = 'Cannot process more than 100 org taxes at a time.';

    public const API_MAX_NOTES_MSG = 'Cannot process more than 100 notes at a time.';

    public const API_MAX_TAGS_MSG = 'Cannot process more than 50 tags at a time.';

    public const API_MAX_RECORD_TAGS_MSG = 'Cannot process more than 10 tags at a time.';

    public const INVALID_DATA = 'INVALID_DATA';

    public const CODE_SUCCESS = 'SUCCESS';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_ERROR = 'error';

    public const SDK_ERROR = 'ZCRM_INTERNAL_ERROR';

    public const LEADS = 'Leads';

    public const ACCOUNTS = 'Accounts';

    public const CONTACTS = 'Contacts';

    public const DEALS = 'Deals';

    public const QUOTES = 'Quotes';

    public const SALESORDERS = 'SalesOrders';

    public const INVOICES = 'Invoices';

    public const PURCHASEORDERS = 'PurchaseOrders';

    public const PER_PAGE = 'per_page';

    public const PAGE = 'page';

    public const COUNT = 'count';

    public const MORE_RECORDS = 'more_records';

    public const ALLOWED_COUNT = 'allowed_count';

    public const MESSAGE = 'message';

    public const CODE = 'code';

    public const STATUS = 'status';

    public const DATA = 'data';

    public const DETAILS = 'details';

    public const MODULES = 'modules';

    public const CUSTOM_VIEWS = 'custom_views';

    public const TAGS = 'tags';

    public const TAXES = 'taxes';

    public const INFO = 'info';

    public const ORG = 'org';

    public const READ = 'read';

    public const RESULT = 'result';

    public const UPLOAD = 'upload';

    public const WRITE = 'write';

    public const CALLBACK = 'callback';

    public const FILETYPE = 'file_type';

    public const QUERY = 'query';

    public const USERS = 'users';

    public const HTTP_CODE = 'http_code';

    public const VARIABLES = 'variables';
    public const RESPONSECODE_OK = 200;

    public const RESPONSECODE_CREATED = 201;

    public const RESPONSECODE_ACCEPTED = 202;

    public const RESPONSECODE_NO_CONTENT = 204;

    public const RESPONSECODE_MOVED_PERMANENTLY = 301;

    public const RESPONSECODE_MOVED_TEMPORARILY = 302;

    public const RESPONSECODE_NOT_MODIFIED = 304;

    public const RESPONSECODE_BAD_REQUEST = 400;

    public const RESPONSECODE_AUTHORIZATION_ERROR = 401;

    public const RESPONSECODE_FORBIDDEN = 403;

    public const RESPONSECODE_NOT_FOUND = 404;

    public const RESPONSECODE_METHOD_NOT_ALLOWED = 405;

    public const RESPONSECODE_REQUEST_ENTITY_TOO_LARGE = 413;

    public const RESPONSECODE_UNSUPPORTED_MEDIA_TYPE = 415;

    public const RESPONSECODE_TOO_MANY_REQUEST = 429;

    public const RESPONSECODE_INTERNAL_SERVER_ERROR = 500;

    public const DOWNLOAD_FILE_PATH = '../../../../../../resources';

    public const USER_EMAIL_ID = 'user_email_id';

    public const ACTION = 'action';

    public const DUPLICATE_FIELD = 'duplicate_field';

    public const ACCESS_TOKEN_EXPIRY = 'X-ACCESSTOKEN-RESET';

    public const CURR_WINDOW_API_LIMIT = 'X-RATELIMIT-LIMIT';

    public const CURR_WINDOW_REMAINING_API_COUNT = 'X-RATELIMIT-REMAINING';

    public const CURR_WINDOW_RESET = 'X-RATELIMIT-RESET';

    public const API_COUNT_REMAINING_FOR_THE_DAY = 'X-RATELIMIT-DAY-REMAINING';

    public const API_LIMIT_FOR_THE_DAY = 'X-RATELIMIT-DAY-LIMIT';

    public const APPLICATION_LOGFILE_PATH = 'applicationLogFilePath';

    public const APPLICATION_LOGFILE_NAME = '/ZCRMClientLibrary.log';

    public const CURRENT_USER_EMAIL = 'currentUserEmail';

    public const FILE_UPLOAD_URL = 'fileUploadUrl';

    public const SANDBOX = 'sandbox';

    public const API_BASE_URL = 'apiBaseUrl';

    public const API_VERSION = 'apiVersion';

    public const BULK_WRITE_STATUS = 'STATUS';

    public const WRITE_STATUS = ['ADDED', 'UPDATED'];

    public const INVENTORY_MODULES = ['Invoices', 'Sales_Orders', 'Purchase_Orders', 'Quotes'];
}
