<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Driver;


use Doctrine\Common\Annotations\Reader;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Base;
use Faker\UniqueGenerator;
use InvalidArgumentException;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;
use OrangeRT\AnonymizeBundle\Exception\InvalidAnonymizeAnnotationException;
use OrangeRT\AnonymizeBundle\Exception\InvalidFunctionException;
use OrangeRT\AnonymizeBundle\Mapping\Anonymize;
use OrangeRT\AnonymizeBundle\Mapping\AnonymizeEntity;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedClassMetadata;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedMethodMetadata;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedPropertyMetadata;
use OrangeRT\AnonymizeBundle\Provider\ChuckNorrisProvider;

class AnonymizeDriver implements DriverInterface
{
    private $reader;
    private $generator;

    public function __construct(Reader $reader, $locale = 'nl_NL')
    {
        $this->reader = $reader;
        $this->generator = Factory::create($locale);
        $this->generator->addProvider(new ChuckNorrisProvider($this->generator));
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return \Metadata\ClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new AnonymizedClassMetadata($class->getName());

        $this->buildClassMetadata($class, $classMetadata);

        $this->buildPropertyMetadata($class, $classMetadata);

        $this->buildMethodMetadata($class, $classMetadata);

        return $classMetadata;
    }

    /**
     * @param \ReflectionClass $class
     * @param AnonymizedClassMetadata $classMetadata
     * @throws InvalidAnonymizeAnnotationException
     */
    private function buildClassMetadata(\ReflectionClass $class, $classMetadata)
    {
        /** @var AnonymizeEntity $annotation */
        $annotation = $this->reader->getClassAnnotation($class, AnonymizeEntity::class);

        if ($annotation !== null) {
            foreach ($annotation->getExclusions() as $property => $regex) {
                if (!$class->hasProperty($property)) {
                    throw new InvalidAnonymizeAnnotationException(sprintf("The expected property %s doesn\'t exist in class %s", $property, $class->getName()));
                }
            }

            foreach ($annotation->getInclusions() as $property => $regex) {
                if (!$class->hasProperty($property)) {
                    throw new InvalidAnonymizeAnnotationException(sprintf("The expected property %s doesn\'t exist in class %s", $property, $class->getName()));
                }
            }
            if (count($annotation->getInclusions()) > 0) {
                $classMetadata->setMatchers($annotation->getInclusions());
                $classMetadata->setMethod(AnonymizedClassMetadata::INCLUDE);
            } else {
                $classMetadata->setMatchers($annotation->getExclusions());
                $classMetadata->setMethod(AnonymizedClassMetadata::EXCLUDE);
            };
            $classMetadata->setCouldExclude(true);
        }
    }

    /**
     * @param \ReflectionClass $class
     * @param ClassMetadata $classMetadata
     */
    private function buildPropertyMetadata(\ReflectionClass $class, $classMetadata)
    {
        foreach ($class->getProperties() as $reflectionProperty) {
            $propertyMetadata = new AnonymizedPropertyMetaData($class->getName(), $reflectionProperty->getName());

            /** @var Anonymize $annotation */
            $annotation = $this->reader->getPropertyAnnotation($reflectionProperty, Anonymize::class);

            if ($annotation !== null) {

                $factory = $this->createFactory($annotation->getFaker(), $class->getName() . '::' . $reflectionProperty->getName());

                if ($annotation->isUnique()) {
                    $propertyMetadata->setGenerator($factory->unique());
                } else {
                    $propertyMetadata->setGenerator($factory);
                }
                $propertyMetadata->setArguments($annotation->getFakerArguments());
                $propertyMetadata->setProperty($annotation->getFaker());
                $propertyMetadata->setExcluded($annotation->getExcluded());

                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }
    }

    /**
     * @param        $function
     *
     * @param string $name
     *
     * @return Generator
     * @throws InvalidFunctionException
     */
    private function createFactory($function, string $name)
    {
        try {
            $this->generator->getFormatter($function);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidFunctionException($function, $name, $e);
        }

        return $this->generator;
    }

    /**
     * @param \ReflectionClass $class
     * @param ClassMetadata $classMetadata
     * @throws \InvalidArgumentException
     */
    private function buildMethodMetadata(\ReflectionClass $class, $classMetadata)
    {
        foreach ($class->getMethods() as $reflectionMethod) {
            $methodMetaData = new AnonymizedMethodMetadata($class->getName(), $reflectionMethod->getName());

            $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Anonymize::class);

            if ($annotation !== null) {

                $factory = null;
                $parameter = null;
                $arguments = [];
                foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                    $type = $reflectionParameter->getType();
                    if ($type === null) {
                        $arguments = $this->lookupParameter($reflectionParameter, $annotation, $arguments, $reflectionMethod);
                    } else {
                        $typeName = $type->getName();
                        if (is_a($typeName, Base::class, true)) {
                            $factory = new $typeName($this->generator);
                            $arguments[$reflectionParameter->name] = $factory;
                        } else if (is_a($typeName, Generator::class, true)) {
                            $factory = $this->generator;
                            $arguments[$reflectionParameter->name] = $factory;
                        } else if (is_a($typeName, UniqueGenerator::class, true)) {
                            $factory = $this->generator->unique();
                            $arguments[$reflectionParameter->name] = $factory;
                        } else {
                            $arguments = $this->lookupParameter($reflectionParameter, $annotation, $arguments, $reflectionMethod);
                        }
                    }
                }

                $methodMetaData->setArguments($arguments);

                $classMetadata->addMethodMetadata($methodMetaData);
            }
        }
    }

    /**
     * @param $reflectionParameter
     * @param $annotation
     * @param $arguments
     * @param $reflectionMethod
     * @return mixed
     */
    private function lookupParameter($reflectionParameter, $annotation, $arguments, $reflectionMethod)
    {
        if (!array_key_exists($reflectionParameter->name, $annotation->getArguments())) {
            $arguments[$reflectionParameter->name] = $annotation->getArguments()[$reflectionParameter->name];
        } else {
            throw new InvalidArgumentException(sprintf('Didn\'t know how to inject class %s for argument %s',
                $reflectionParameter->name, $reflectionMethod->name), 2003);
        }
        return $arguments;
    }
}