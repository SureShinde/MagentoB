<?php

namespace Bilna\Libraries;

class DefaultConstant {

    // Response code
    const RCODE_OK = 200;
    const RCODE_INPUT_STRUCTURE = 401;
    const RCODE_INPUT_NOTCOMPLETED = 402;
    const RCODE_INPUT_TYPE = 403;
    const RCODE_RECORD_EXIST = 501;
    const RCODE_RECORD_NOTFOUND = 502;
    const RCODE_INPUT_VALUE = 503;
    const RCODE_API_INVALID_INPUT = 801;
    const RCODE_API_UNAUTHORIZED_CLIENTID = 802;
    const RCODE_API_UNAUTHORIZED_CLIENTTOKEN = 803;
    const RCODE_FAILED_ORM = 804;
    const RCODE_UNKNOWN = 901;
    // Response code message
    const RCODE_OK_MESSAGE = "OK";
    const RCODE_INPUT_STRUCTURE_MESSAGE = "Bad parameter input";
    const RCODE_INPUT_NOTCOMPLETED_MESSAGE = "Parameter is not completed";
    const RCODE_INPUT_TYPE_MESSAGE = "Wrong parameter type";
    const RCODE_RECORD_EXIST_MESSAGE = "Record already exists";
    const RCODE_RECORD_NOTFOUND_MESSAGE = "Record not found";
    const RCODE_INPUT_VALUE_MESSAGE = "Wrong parameter value";
    const RCODE_API_INVALID_INPUT_MESSAGE = "Invalid JSON Format";
    const RCODE_API_UNAUTHORIZED_CLIENTID_MESSAGE = "Unauthorized clientId";
    const RCODE_API_UNAUTHORIZED_CLIENTTOKEN_MESSAGE = "Unauthorized clientToken";
    const RCODE_FAILED_ORM_MESSAGE = "ORM Failed";
    const RCODE_UNKNOWN_MESSAGE = "Something went wrong, please try again";

    public static function getResponseCode() {
        return array(
            self::RCODE_OK => self::RCODE_OK_MESSAGE,
            self::RCODE_INPUT_STRUCTURE => self::RCODE_INPUT_STRUCTURE_MESSAGE,
            self::RCODE_INPUT_NOTCOMPLETED => self::RCODE_INPUT_NOTCOMPLETED_MESSAGE,
            self::RCODE_INPUT_TYPE => self::RCODE_INPUT_TYPE_MESSAGE,
            self::RCODE_RECORD_EXIST => self::RCODE_RECORD_EXIST_MESSAGE,
            self::RCODE_RECORD_NOTFOUND => self::RCODE_RECORD_NOTFOUND_MESSAGE,
            self::RCODE_INPUT_VALUE => self::RCODE_INPUT_VALUE_MESSAGE,
            self::RCODE_API_INVALID_INPUT => self::RCODE_API_INVALID_INPUT_MESSAGE,
            self::RCODE_API_UNAUTHORIZED_CLIENTID => self::RCODE_API_UNAUTHORIZED_CLIENTID_MESSAGE,
            self::RCODE_API_UNAUTHORIZED_CLIENTTOKEN => self::RCODE_API_UNAUTHORIZED_CLIENTTOKEN_MESSAGE,
            self::RCODE_FAILED_ORM => self::RCODE_FAILED_ORM_MESSAGE,
            self::RCODE_UNKNOWN => self::RCODE_UNKNOWN_MESSAGE
        );
    }

}
