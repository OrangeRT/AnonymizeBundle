<?php


namespace OrangeRT\AnonymizeBundle\Command;


use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnonymizeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('anonymizer:anonymize');
        $this->setDescription('Anonymize the database, based on the anonymize tags.');
        $this->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use', 'default');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, "Prints out the entities with their properties to change");
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces the anonimizing');
        $this->addOption('paging', 'p', InputOption::VALUE_OPTIONAL, 'The amount of entities to modify in a single batch', AnonymizeProcessor::BATCH_SIZE);
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');

        $em = $doctrine->getManager($input->getOption('em'));

        $metaData = $em->getMetadataFactory()->getAllMetadata();

        foreach($metaData as $classMetadata) {
            $output->writeln($classMetadata->getName());
        }
    }
}