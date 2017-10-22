<?php
/******************************************************************************
 * Copyright (c) 2017.                                                        *
 ******************************************************************************/

namespace OrangeRT\AnonymizeBundle\Command;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnonymizeDebugCommand extends ContainerAwareCommand
{
    public function run(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        if (!$this->getContainer()->has('doctrine')) {
            throw new \Exception('Doctrine was not found in the container', 2);
        }
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');

        $entityManager = $doctrine->getManager($input->getOption('em'));

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException('Expected a EntityManagerInterface, got ' . get_class($entityManager));
        }

        $anonymizer = $this->getContainer()->get('orange_rt_anonymize.metadata.processor.debug');

        $anonymizer->setStyle($style);

        $anonymizer->anonymize($entityManager);
    }

    protected function configure()
    {
        $this->setName('anonymizer:debug');
        $this->setDescription('Anonymize the database, based on the anonymize tags.');
        $this->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use', 'default');
    }
}