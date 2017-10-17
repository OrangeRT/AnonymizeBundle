<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Metadata;


use Faker\Generator;
use Faker\UniqueGenerator;
use InvalidArgumentException;
use Metadata\PropertyMetadata;

class AnonymizedPropertyMetadata extends PropertyMetadata
{

    /**
     * @var Generator
     */
    private $generator;

    /** @var string */
    private $property;

    /** @var array */
    private $arguments;

    /**
     * @var array
     */
    private $excluded = array();

    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
    }

    public function setValue($obj, $value = null)
    {
        if ($value === null) {
            if ($this->getValue($obj) === null) {
                return;
            }
            $originalValue = (string)$this->getValue($obj);
            foreach ($this->excluded as $item) {
                if (preg_match($item, $originalValue)) {
                    return;
                }
            }

            if (is_callable(array($this->generator, $this->property))) {
                $value = call_user_func_array(array($this->generator, $this->property), $this->arguments);
            } else {
                $value = $this->generator->${$this->property};
            }
        }
        parent::setValue($obj, $value);
    }

    /**
     * @return Generator|UniqueGenerator
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    public function setGenerator($generator)
    {
        if ($generator instanceof Generator || $generator instanceof UniqueGenerator) {
            $this->generator = $generator;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid argument, expected one one \'Faker\\Generator\' or \'Faker\\UniqueGenerator\', got %s',
                get_class($generator)), 2002);
        }
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property)
    {
        $this->property = $property;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array
     */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;
    }
}