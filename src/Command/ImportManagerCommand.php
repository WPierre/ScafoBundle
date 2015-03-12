<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WPierre\Scafo\ScafoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Output\OutputInterface,
    WPierre\Scafo\ScafoBundle\Entity\Filter,
    WPierre\Scafo\ScafoBundle\Repository\FilterRepository,
    WPierre\Scafo\ScafoBundle\Entity\Document,
    WPierre\Scafo\ScafoBundle\Classes\FilesOperations,
    WPierre\Scafo\ScafoBundle\Classes\BatchOperations,
    Smalot\PdfParser\Parser;

    

/**
 * Description of ImportManager
 *
 * @author Ashygan
 */
class ImportManagerCommand extends ContainerAwareCommand {
    
    /**
     * @var ConfigInstance L'instance de configuration sur laquelle l'exécution tourne 
     */
    private $config_instance;
    
    /**
     *
     * @var OutputInterface L'interface de sortie 
     */
    private $output;
    
    protected function configure()
    {
        $this
            ->setName('wpierre:importmanager')
            ->setDescription('This command runs the index process for a defined profile')
            ->addArgument(
                'profile',
                InputArgument::REQUIRED,
                'The profile to be used for index'
            );
        
    }
    
    /**
     * Execute the command
     * The environment option is automatically handled.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //storing output so we can access from other methods
        $this->output = $output;
        
        $logger = $this->getContainer()->get('logger');
    	$logger->info('Starting the index task...');
        
        //trying to get the profile asked for
        $profile = $input->getArgument('profile');
        
        $this->config_instance = $this->getContainer()->get('doctrine')->getRepository('WPierreScafoScafoBundle:ConfigInstance')->findOneByInstanceName($profile);
        $batchOperations = new BatchOperations($this->config_instance->getId(), $this->getContainer());
        $this->output = $batchOperations->executeFullRun();
        return $this->output;
    }
}
