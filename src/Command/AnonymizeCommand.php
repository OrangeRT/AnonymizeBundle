<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Command;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use OrangeRT\AnonymizeBundle\Processor\AnonymizeProcessor;
use OrangeRT\AnonymizeBundle\Processor\AnonymizeStopwatchProcessor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnonymizeCommand extends ContainerAwareCommand
{
    public function run(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        if (!$this->getContainer()->has('doctrine')) {
            throw new \Exception('Doctrine was not found in the container', 2);
        }
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');

        /** @var Connection $connection */
        $connection = $doctrine->getConnection($input->getOption('em'));

        if (!$input->getOption('force') && !$style->confirm(sprintf('You are about to anonymize the %s on host %s, are you sure?', $connection->getDatabase(), $connection->getHost()))) {
            return;
        }

        $entityManager = $doctrine->getManager($input->getOption('em'));

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException('Expected a EntityManagerInterface, got '.get_class($entityManager));
        }

        $anonymizer = AnonymizeStopwatchProcessor::fromAnonymizer($this->getContainer()->get('orange_rt_anonymize.metadata.processor'));

        $anonymizer->anonymize($entityManager, $input->getOption('paging'));

        $watch = $anonymizer->getStopwatch();

        foreach ($watch->getSections() as $sectionName => $section) {
            if ($sectionName === '__root__') {
                continue;
            }
            $style->section($section->getId());
            $headers = ['Name', 'Start', 'End', 'Duration', 'Memory'];
            $rows = [];
            foreach ($section->getEvents() as $eventName => $event) {
                if ($eventName === '__section__') {
                    continue;
                }
                $rows[] = [$eventName, $event->getStartTime(), $event->getEndTime(), $event->getDuration(), $event->getMemory()];
            }
            $style->table($headers, $rows);
        }
    }

    protected function configure()
    {
        $this->setName('anonymizer:anonymize');
        $this->setDescription('Anonymize the database, based on the anonymize tags.');
        $this->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use', 'default');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces the anonimizing');
        $this->addOption('paging', 'p', InputOption::VALUE_OPTIONAL, 'The amount of entities to modify in a single batch', AnonymizeProcessor::BATCH_SIZE);
    }
}