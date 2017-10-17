<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle;


use Doctrine\Common\Persistence\ObjectManager;
use Metadata\MetadataFactoryInterface;
use OrangeRT\AnonymizeBundle\Metadata\AnonymizedClassMetadata;
use Symfony\Component\Stopwatch\Stopwatch;

class AnonymizeStopwatchProcessor implements IAnonymizer
{
    const BATCH_SIZE = 500;
    const STOPWATCH_EVENT = 'Processing entities';

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    private $stopwatch;

    private $delegate;

    /**
     * AnonymizerProcessor constructor.
     * @param MetadataFactoryInterface $metadataFactory
     * @param IAnonymizer|null $anonymizer
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, IAnonymizer $anonymizer = null)
    {
        $this->stopwatch = new Stopwatch();
        $this->delegate = $anonymizer ?? new AnonymizeProcessor($metadataFactory);
    }

    public static function fromAnonymizer(IAnonymizer $anonymizer)
    {
        return new self($anonymizer->getMetadataFactory(), $anonymizer);
    }

    public function anonymize(ObjectManager $manager, int $batchSize = self::BATCH_SIZE)
    {;
        $this->stopwatch->openSection(self::STOPWATCH_EVENT);
        foreach($manager->getMetadataFactory()->getAllMetadata() as $classMetadata)
        {
            $this->anonymizeClass($manager, $classMetadata->getName(), $batchSize);
        }
        $this->stopwatch->stopSection(self::STOPWATCH_EVENT);
    }

    public function anonymizeClass(ObjectManager $manager, $class, int $batchSize = self::BATCH_SIZE)
    {
        $anonymizedData = $this->metadataFactory->getMetadataForClass($class);
        if ($anonymizedData instanceof AnonymizedClassMetadata && (count($anonymizedData->propertyMetadata) > 0 || count($anonymizedData->methodMetadata) > 0))
        {
            $event = $this->stopwatch->start(sprintf('Anonymizing %s', $class));
            $this->delegate->anonymizeClass($manager, $class, $batchSize);
            $event->stop();
        }
    }

    /**
     * @return Stopwatch
     */
    public function getStopwatch(): Stopwatch
    {
        return $this->stopwatch;
    }

    /**
     * @return MetadataFactoryInterface
     */
    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }
}