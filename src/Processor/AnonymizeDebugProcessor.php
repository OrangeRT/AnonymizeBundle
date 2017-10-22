<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Processor;


use Doctrine\ORM\EntityManagerInterface;
use Metadata\MetadataFactoryInterface;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedClassMetadata;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedMethodMetadata;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedPropertyMetadata;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnonymizeDebugProcessor implements IAnonymizer
{
    /** @var SymfonyStyle */
    private $style;
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * AnonymizerProcessor constructor.
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function anonymize(EntityManagerInterface $manager, int $batchSize = self::BATCH_SIZE)
    {
        if ($this->style === null) {
            throw new \RuntimeException('The style is not set, not outputting anything');
        }

        foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            $this->anonymizeClass($manager, $classMetadata->getName(), $batchSize);
        }
    }

    public function anonymizeClass(EntityManagerInterface $manager, $class, int $batchSize = self::BATCH_SIZE)
    {
        /** @var AnonymizedClassMetadata $anonymizedData */
        $anonymizedData = $this->metadataFactory->getMetadataForClass($class);
        if (!$anonymizedData) {
            throw new \RuntimeException("Couldn't load the metadata for class " . $class);
        }
        $anonymizedPropertyMetadata = array_filter($anonymizedData->propertyMetadata, function ($metadata) {
            return $metadata instanceof AnonymizedPropertyMetadata;
        });
        $anonymizedMethodMetadata = array_filter($anonymizedData->methodMetadata, function ($metadata) {
            return $metadata instanceof AnonymizedMethodMetadata;
        });

        $numProperties = count($anonymizedPropertyMetadata);
        $numMethods = count($anonymizedMethodMetadata);
        if ($numMethods > 0 || $numProperties > 0) {
            $this->style->block($class);
            $this->style->section('Class metadata');

            $this->style->table(
                ['Method', 'Matchers'],
                [[$anonymizedData->getMethod() === 0 ? 'Include' : 'Exclude', implode(', ', array_map(function ($value, $key) {
                    return sprintf('%s => %s', $key, $value);
                }, $anonymizedData->getMatchers(), array_keys($anonymizedData->getMatchers())))]]
            );

            if ($numProperties > 0) {
                $this->style->section('Anonymized properties');

                $this->style->table(
                    array('Property', 'Faker', 'FakerMethod', 'Arguments'),
                    array_map(function (AnonymizedPropertyMetadata $data) {
                        return [$data->name, get_class($data->getGenerator()), $data->getProperty(), implode(', ', $data->getArguments())];
                    }, $anonymizedPropertyMetadata)
                );
            }

            if ($numMethods > 0) {
                $this->style->section('Anonymized methods');
                $this->style->table(
                    array('Method', 'Arguments'),
                    array_map(function (AnonymizedMethodMetadata $data) {
                        return [$data->name, implode(', ', array_map(function ($obj) {
                            if (is_object($obj)) {
                                if (method_exists($obj, '__toString')) {
                                    return $obj->__toString();
                                }
                                return get_class($obj);
                            }
                            if (is_array($obj)) {
                                return implode(', ', $obj);
                            }
                            return $obj;
                        }, $data->getArguments()))];
                    }, $anonymizedMethodMetadata)
                );
            }
        }
    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    /**
     * @param SymfonyStyle $style
     */
    public function setStyle(SymfonyStyle $style)
    {
        $this->style = $style;
    }
}