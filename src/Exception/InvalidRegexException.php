<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Exception;


class InvalidRegexException extends \Exception
{
    public function __construct($regex, \Throwable $previous = null)
    {
        $errors = array(
            PREG_NO_ERROR              => 'Code 0 : No errors',
            PREG_INTERNAL_ERROR        => 'Code 1 : There was an internal PCRE error',
            PREG_BACKTRACK_LIMIT_ERROR => 'Code 2 : Backtrack limit was exhausted',
            PREG_RECURSION_LIMIT_ERROR => 'Code 3 : Recursion limit was exhausted',
            PREG_BAD_UTF8_ERROR        => 'Code 4 : The offset didn\'t correspond to the begin of a valid UTF-8 code point',
            PREG_BAD_UTF8_OFFSET_ERROR => 'Code 5 : Malformed UTF-8 data',
        );

        return parent::__construct(sprintf('The regex [%s] was invalid: %s', $regex, $errors[preg_last_error()]), 2001, $previous);
    }
}