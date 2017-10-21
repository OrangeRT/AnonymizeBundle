<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Processor;


use Doctrine\ORM\EntityManagerInterface;
use Metadata\MetadataFactoryInterface;

interface IAnonymizer
{
    const BATCH_SIZE = 500;
    public function __construct(MetadataFactoryInterface $metadataFactory);

    public function anonymize(EntityManagerInterface $manager, int $batchSize = self::BATCH_SIZE);
    public function anonymizeClass(EntityManagerInterface $manager, $class, int $batchSize = self::BATCH_SIZE);

    public function getMetadataFactory():MetadataFactoryInterface;
}