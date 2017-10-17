<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle;


use Doctrine\Common\Persistence\ObjectManager;
use Metadata\MetadataFactoryInterface;

interface IAnonymizer
{
    const BATCH_SIZE = 500;
    public function __construct(MetadataFactoryInterface $metadataFactory);

    public function anonymize(ObjectManager $manager, int $batchSize = self::BATCH_SIZE);
    public function anonymizeClass(ObjectManager $manager, $class, int $batchSize = self::BATCH_SIZE);

    public function getMetadataFactory():MetadataFactoryInterface;
}