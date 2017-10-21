<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Mapping;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use OrangeRT\AnonymizeBundle\Exception\InvalidAnonymizeAnnotationException;


/**
 * Class Anonymize
 * @package OrangeRT\AnonymizeBundle\Mapping
 *
 * @Annotation
 * @Target(value={"PROPERTY","METHOD"})
 */
class Anonymize
{
    /**
     * @var string The provider to call on the faker generator.
     */
    private $faker;

    /**
     * @var array An optional array of arguments to pass to the faker.
     */
    private $fakerArguments = [];

    /**
     * @var array|string[] An array of regular expressions. Excludes the current property of the entity if one of the regular expressions matches.
     */
    private $excluded = [];

    /**
     * @var string A regular expression or a string to match the value against. If the value matches, set the property.
     */
    private $exclude = null;

    /**
     * @var boolean Whether the values in the database should be unique.
     */
    private $unique = false;

    public function __construct(array $options)
    {
        $this->faker = $options['faker'] ?? '';
        $this->fakerArguments = $options['fakerArguments'] ?? [];
        $this->excluded = $options['excluded'] ?? [];
        $this->exclude = $options['exclude'] ?? null;
        $this->unique = $options['unique'] ?? false;

        if ($this->exclude !== null && count($this->excluded) > 0) {
            throw new InvalidAnonymizeAnnotationException('You can\'t set both the excluded array and the exclude annotation.');
        }
    }

    /**
     * @return string
     */
    public function getFaker(): string
    {
        return $this->faker;
    }

    /**
     * @return array
     */
    public function getFakerArguments(): array
    {
        return $this->fakerArguments;
    }

    /**
     * @return array|string[]
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

    /**
     * @return string
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

}