<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Exception;


class InvalidFunctionException extends \Exception
{
    public function __construct($function, $location, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Invalid function at %s, the function [%s] Doesn\'t exist in the generator', $location, $function), 2008,
            $previous);
    }

}