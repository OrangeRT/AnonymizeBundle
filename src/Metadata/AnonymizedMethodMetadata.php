<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Metadata;


use Metadata\MethodMetadata;

class AnonymizedMethodMetadata extends MethodMetadata
{
    /**
     * @var array
     */
    private $arguments = array();

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function invoke($obj, array $args = array())
    {
        return parent::invoke($obj, $this->arguments);
    }
}