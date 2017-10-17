<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Processor;


use Doctrine\Common\Persistence\ObjectManager;
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

    public function anonymize(ObjectManager $manager, int $batchSize = self::BATCH_SIZE)
    {
    }

    public function anonymizeClass(ObjectManager $manager, $class, int $batchSize = self::BATCH_SIZE)
    {

    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }
}