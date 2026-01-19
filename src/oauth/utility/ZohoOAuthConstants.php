<?php

namespace zcrmsdk\oauth\utility;

class ZohoOAuthConstants
{
    public const string IAM_URL = 'accounts_url';

    public const string SCOPES = 'scope';

    public const string STATE = 'state';

    public const string STATE_OBTAINING_GRANT_TOKEN = 'OBTAIN_GRANT_TOKEN';

    public const string RESPONSE_TYPE = 'response_type';

    public const string RESPONSE_TYPE_CODE = 'code';

    public const string CLIENT_ID = 'client_id';

    public const string CLIENT_SECRET = 'client_secret';

    public const string REDIRECT_URL = 'redirect_uri';

    public const string ACCESS_TYPE = 'access_type';

    public const string ACCESS_TYPE_OFFLINE = 'offline';

    public const string ACCESS_TYPE_ONLINE = 'online';

    public const string PROMPT = 'prompt';

    public const string PROMPT_CONSENT = 'consent';

    public const string GRANT_TYPE = 'grant_type';

    public const string GRANT_TYPE_AUTH_CODE = 'authorization_code';

    public const string GRANT_TYPE_REFRESH = 'refresh_token';

    public const string CODE = 'code';

    public const string GRANT_TOKEN = 'grant_token';

    public const string ACCESS_TOKEN = 'access_token';

    public const string REFRESH_TOKEN = 'refresh_token';

    public const string EXPIRES_IN = 'expires_in';

    public const string EXPIRES_IN_SEC = 'expires_in_sec';

    public const string EXPIRY_TIME = 'expiry_time';

    public const string PERSISTENCE_HANDLER_CLASS = 'persistence_handler_class';

    public const string PERSISTENCE_HANDLER_CLASS_NAME = 'persistence_handler_class_name';

    public const string TOKEN = 'token';

    public const string DISPATCH_TO = 'dispatchTo';

    public const string OAUTH_TOKENS_PARAM = 'oauth_tokens';

    public const string OAUTH_HEADER_PREFIX = 'Zoho-oauthtoken ';

    public const string AUTHORIZATION = 'Authorization';

    public const string REQUEST_METHOD_GET = 'GET';

    public const string REQUEST_METHOD_POST = 'POST';

    public const string SANDBOX = 'sandbox';

    public const string TOKEN_PERSISTENCE_PATH = 'token_persistence_path';

    public const string DATABASE_NAME = 'db_name';

    public const string DATABASE_PORT = 'db_port';

    public const string DATABASE_USERNAME = 'db_username';

    public const string DATABASE_PASSWORD = 'db_password';

    public const string HOST_ADDRESS = 'host_address';

    public const int RESPONSECODE_OK = 200;
}
