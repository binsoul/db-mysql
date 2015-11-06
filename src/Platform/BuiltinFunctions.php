<?php

namespace BinSoul\Db\Platform\MySQL\Platform;

/**
 * Provides methods related to MySQL builtin functions.
 */
class BuiltinFunctions
{
    /**
     * List of builtin function names.
     *
     * @var string[]
     */
    private static $builtinFunctions = [
        'NOW',
        'IF',
        'IFNULL',
        'NULLIF',
        'ASCII',
        'BIN',
        'BIT_LENGTH',
        'CHAR_LENGTH',
        'CHAR',
        'CONCAT_WS',
        'CONCAT',
        'ELT',
        'EXPORT_SET',
        'FIELD',
        'FIND_IN_SET',
        'FORMAT',
        'FROM_BASE64',
        'HEX',
        'INSERT',
        'INSTR',
        'LCASE',
        'LEFT',
        'LENGTH',
        'LOAD_FILE',
        'LOCATE',
        'LOWER',
        'LPAD',
        'LTRIM',
        'MAKE_SET',
        'OCT',
        'OCTET_LENGTH',
        'ORD',
        'POSITION',
        'QUOTE',
        'REPEAT',
        'REPLACE',
        'REVERSE',
        'RIGHT',
        'RPAD',
        'RTRIM',
        'SOUNDEX',
        'SPACE',
        'STRCMP',
        'SUBSTR',
        'SUBSTRING_INDEX',
        'SUBSTRING',
        'TO_BASE64',
        'TRIM',
        'UCASE',
        'UNHEX',
        'UPPER',
        'WEIGHT_STRING',
        'ABS',
        'ACOS',
        'ASIN',
        'ATAN2',
        'ATAN',
        'CEIL',
        'CEILING',
        'CONV',
        'COS',
        'COT',
        'CRC32',
        'DEGREES',
        'EXP',
        'FLOOR',
        'LN',
        'LOG10',
        'LOG2',
        'LOG',
        'MOD',
        'PI',
        'POW',
        'POWER',
        'RADIANS',
        'RAND',
        'ROUND',
        'SIGN',
        'SIN',
        'SQRT',
        'TAN',
        'TRUNCATE',
        'ADDDATE',
        'ADDTIME',
        'CONVERT_TZ',
        'CURDATE',
        'CURRENT_DATE',
        'CURRENT_TIME',
        'CURRENT_TIMESTAMP',
        'CURTIME',
        'DATE_ADD',
        'DATE_FORMAT',
        'DATE_SUB',
        'DATE',
        'DATEDIFF',
        'DAY',
        'DAYNAME',
        'DAYOFMONTH',
        'DAYOFWEEK',
        'DAYOFYEAR',
        'EXTRACT',
        'FROM_DAYS',
        'FROM_UNIXTIME',
        'GET_FORMAT',
        'HOUR',
        'LOCALTIME',
        'LOCALTIMESTAMP',
        'MAKEDATE',
        'MAKETIME',
        'MICROSECOND',
        'MINUTE',
        'MONTH',
        'MONTHNAME',
        'PERIOD_ADD',
        'PERIOD_DIFF',
        'QUARTER',
        'SEC_TO_TIME',
        'SECOND',
        'STR_TO_DATE',
        'SUBDATE',
        'SUBTIME',
        'SYSDATE',
        'TIME_FORMAT',
        'TIME_TO_SEC',
        'TIME',
        'TIMEDIFF',
        'TIMESTAMP',
        'TIMESTAMPADD',
        'TIMESTAMPDIFF',
        'TO_DAYS',
        'TO_SECONDS',
        'UNIX_TIMESTAMP',
        'UTC_DATE',
        'UTC_TIME',
        'UTC_TIMESTAMP',
        'WEEK',
        'WEEKDAY',
        'WEEKOFYEAR',
        'YEAR',
        'YEARWEEK',
        'CAST',
        'CONVERT',
        'BIT_COUNT',
    ];

    /**
     * Indicates if the given sting contains a call to a builtin function.
     *
     * @param string $string
     *
     * @return bool
     */
    public function containsFunction($string)
    {
        $position = strpos($string, '(');
        if (!$position) {
            return false;
        }

        $target = strtoupper(substr($string, 0, $position));
        if (!preg_match('/([A-Z_0-9]+)\s*$/', $target, $matches)) {
            return false;
        }

        return in_array($matches[0], self::$builtinFunctions);
    }
}
