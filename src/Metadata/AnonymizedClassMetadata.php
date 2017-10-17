<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Metadata;


use Metadata\ClassMetadata;
use Metadata\MergeableInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Class AnonymizedClassMetadata
 * @package OrangeRT\AnonymizeBundle\Metadata
 */
class AnonymizedClassMetadata extends ClassMetadata
{
    const INCLUDE = 0;
    const EXCLUDE = 1;

    private $matchers = [];

    private $method = self::INCLUDE;

    /**
     * @var bool Property to define whether this annotation could be excluded.
     */
    private $couldExclude = false;

    /**
     * @return array
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    /**
     * @param array $matchers
     */
    public function setMatchers(array $matchers)
    {
        $this->matchers = $matchers;
    }

    /**
     * @return int
     */
    public function getMethod(): int
    {
        return $this->method;
    }

    /**
     * @param int $method
     */
    public function setMethod(int $method)
    {
        if (!in_array($method, [self::INCLUDE, self::EXCLUDE])) {
            throw new InvalidArgumentException(sprintf("The method %d is not a valid method", $method));
        }
        $this->method = $method;
    }

    /**
     * @param $object \stdClass|string The entity to check whether it should be included in the anonymization process.
     * @return bool
     */
    public function shouldInclude($object)
    {
        $reflection = new \ReflectionClass($object);
        foreach($this->getMatchers() as $property => $matches)
        {
            if ($reflection->hasProperty($property)) {
                $value = $reflection->getProperty($property)->getValue($object);
                if (is_string($matches) && (stristr($value, $matches) || preg_match($matches, $value))) {
                    return $this->method === self::INCLUDE;
                }
            }
        }
        return $this->method === self::EXCLUDE;
    }

    /**
     * @return bool
     */
    public function isCouldExclude(): bool
    {
        return $this->couldExclude;
    }

    /**
     * @param bool $couldExclude
     */
    public function setCouldExclude(bool $couldExclude)
    {
        $this->couldExclude = $couldExclude;
    }
}