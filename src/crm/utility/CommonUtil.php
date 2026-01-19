<?php

namespace zcrmsdk\crm\utility;

class CommonUtil
{
    public static function getEmptyJSONObject(): \ArrayObject
    {
        return new \ArrayObject();
    }

    public static function removeNullValuesAlone(mixed $value): bool
    {
        return null !== $value;
    }
}
