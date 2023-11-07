<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Exception\InvalidOptionException;

class AlgoliaImportCommand extends Command 
{
    const APPLICATION_ID = 'application-id';
    const API_KEY = 'api-key';

    protected function configure()
    {
        $this->setName('algolia-import')
        ->setDescription('Imports Algolia sample data')
        ->addOption(self::APPLICATION_ID, null, InputOption::VALUE_REQUIRED, 'Algolia Application ID')
        ->addOption(self::API_KEY, null, InputOption::VALUE_REQUIRED, 'Algolia API Key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $applicationId = $input->getOption(self::APPLICATION_ID);
        $apiKey = $input->getOption(self::API_KEY);

        // Input validation guard clauses
        if(empty($applicationId)) {
            throw new InvalidOptionException('Application ID is required');
        }

        if(empty($apiKey)) {
            throw new InvalidOptionException('API Key is required');
        }

        $now = date('c');
        $message = sprintf("Current date and time: %s", $now);
        $output->writeln($message);

        

        return Command::SUCCESS;
    }
}
