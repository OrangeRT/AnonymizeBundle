<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Mapping;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use OrangeRT\AnonymizeBundle\Exception\InvalidAnonymizeAnnotationException;

/**
 * Class Exclude
 * @package OrangeRT\AnonymizeBundle\Mapping
 * @Annotation
 * @Target(value="CLASS")
 */
class AnonymizeEntity
{
    /**
     * @var array The entities to exclude. "property" => "value". If any of the property matches, do not update this entity.
     */
    private $exclusions = array();

    /**
     * @var array The entities to include. "property" => "value". If any of the property matches the value, update this entity.
     */
    private $inclusions = array();

    public function __construct($options)
    {
        $this->exclusions = $options['exclusions'] ?? [];
        $this->inclusions = $options['inclusions'] ?? [];

        if (!(count($this->inclusions) > 0 ^ count($this->exclusions) > 0)) {
            throw new InvalidAnonymizeAnnotationException('Can\'t set both the inclusions and the exclusions');
        }
    }

    /**
     * @return array
     */
    public function getExclusions(): array
    {
        return $this->exclusions;
    }

    /**
     * @return array
     */
    public function getInclusions(): array
    {
        return $this->inclusions;
    }
}