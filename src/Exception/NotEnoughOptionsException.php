<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Exception;


class NotEnoughOptionsException extends \Exception
{
    public function __construct(array $required, array $given, \Throwable $previous = null)
    {
        parent::__construct(sprintf('The [%s] options are required, [%s] given', join(', ', $required), join(', ', array_keys($given))), 2009,
            $previous);
    }
}