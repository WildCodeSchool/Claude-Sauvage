<?php
namespace CS\GedBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use CS\GedBundle\Entity\Category;
 
class ControllerGeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ged:init');
    }
 
    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // On récupère l'EntityManager
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        //ajout d'une nouvelle catégorie 
        $Brouillon = new Category();
        $Brouillon->setName('brouillon');
        
        //on envois en BDD
        $em->persist($Brouillon);
        $em->flush();

        $output->writeln('ged initialisé.');

        return $output;
    }
}