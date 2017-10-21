<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Processor;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Metadata\MetadataFactoryInterface;

class AnonymizeDebugProcessor implements IAnonymizer
{
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
    }

    public function anonymizeClass(EntityManagerInterface $manager, $class, int $batchSize = self::BATCH_SIZE)
    {

    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }
}