<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Processor;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Metadata\MetadataFactoryInterface;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedClassMetadata;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedMethodMetadata;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedPropertyMetadata;

class AnonymizeProcessor implements IAnonymizer
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
        foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            $this->anonymizeClass($manager, $classMetadata->getName(), $batchSize);
        }
    }

    public function anonymizeClass(EntityManagerInterface $manager, $class, int $batchSize = self::BATCH_SIZE)
    {
        /** @var AnonymizedClassMetadata $anonymizedData */
        $anonymizedData = $this->metadataFactory->getMetadataForClass($class);
        if (!$anonymizedData) {
            throw new \RuntimeException("Couldn't load the metadata for class ".$class);
        }
        $anonymizedPropertyMetadata = array_filter($anonymizedData->propertyMetadata, function($metadata) {
            return $metadata instanceof AnonymizedPropertyMetadata;
        });
        $anonymizedMethodMetadata = array_filter($anonymizedData->methodMetadata, function($metadata) {
            return $metadata instanceof AnonymizedMethodMetadata;
        });

        if (count($anonymizedMethodMetadata) > 0 || count($anonymizedPropertyMetadata) > 0) {
            /** @var EntityRepository $repository */
            $repository = $manager->getRepository($class);

            $qb = $repository->createQueryBuilder('c');

            $count = (clone $qb)->select('COUNT(c)')->getQuery()->getSingleScalarResult();


            $page = 0;
            $pages = ceil($count / $batchSize);

            while ($page < $pages) {
                $objects = (clone $qb)
                    ->setFirstResult($page * $batchSize)
                    ->setMaxResults($batchSize)
                    ->getQuery()
                    ->getResult();

                foreach ($objects as $object) {
                    if ($anonymizedData->isCouldExclude() && !$anonymizedData->shouldInclude($object)) {
                        continue;
                    }
                    /** @var AnonymizedPropertyMetaData $anonymizedProperty */
                    foreach ($anonymizedPropertyMetadata as $anonymizedProperty) {
                        $anonymizedProperty->setValue($object);
                    }

                    /** @var AnonymizedMethodMetadata $anonymizedMethod */
                    foreach ($anonymizedMethodMetadata as $anonymizedMethod) {
                        $anonymizedMethod->invoke($object);
                    }
                }
                $manager->flush();
                $manager->clear();
                ++$page;
            }
        }
    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }
}