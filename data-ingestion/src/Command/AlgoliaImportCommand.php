<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Symfony\Component\Console\Style\SymfonyStyle;

class AlgoliaImportCommand extends Command 
{
    const APPLICATION_ID = 'application-id';
    const API_KEY = 'api-key';
    const INDEX_NAME = 'index-name';
    const INPUT_FILENAME = 'input-filename';
    const BATCH_SIZE = 'batch-size';
    const BATCH_SIZE_DEFAULT = 10;

    protected function configure()
    {
        $this->setName('algolia-import')
        ->setDescription('Imports Algolia sample data')
        ->addOption(self::APPLICATION_ID, null, InputOption::VALUE_REQUIRED, 'Algolia Application ID')
        ->addOption(self::API_KEY, null, InputOption::VALUE_REQUIRED, 'Algolia API Key')
        ->addOption(self::INDEX_NAME, null, InputOption::VALUE_REQUIRED, 'Algolia Index Name')
        ->addOption(self::INPUT_FILENAME, null, InputOption::VALUE_REQUIRED, 'Input Filename')
        ->addOption(self::BATCH_SIZE, null, InputOption::VALUE_REQUIRED, 'Data load batchs size', self::BATCH_SIZE_DEFAULT);
    }

    protected function getImportData(string $inputFilename) : array
    {
        // Basic file check
        if(!file_exists($inputFilename)) {
            throw new RuntimeException(sprintf('Invalid input file %s.', $inputFilename));
        }

        // Speculative check for file contents
        $fileContents = file_get_contents($inputFilename);
        if(empty($fileContents)) {
            throw new RuntimeException(sprintf('Invalid input file contents at %s.', $inputFilename));
        }

        // Speculative check for valid JSON
        $data = json_decode($fileContents, true);
        if(is_null($data)) {
            throw new RuntimeException(sprintf('Invalid input file JSON at %s.', $inputFilename));
        }

        return $data;
    }

    protected function importDataBatch(array $batchData, Searchindex $algoliaIndex) {
        // Intentionally not catching exceptions at this point
        $algoliaIndex->saveObjects($batchData);
    }

    protected function importData(
        array $data,
        int $batchSize,
        SearchIndex $algoliaIndex,
        callable $progressCallback,
        callable $errorCallback
    ) {
        $i = 0;
        $batch = array_slice($data, $i, $batchSize);

        while(!empty($batch)) {
            try {
                $this->importDataBatch($batch, $algoliaIndex);
            } catch (\Exception $e) {
                $errorCallback($e, $batch);
            }

            $progressCallback(count($batch));

            // prep for next iteration
            $i += $batchSize;
            $batch = array_slice($data, $i, $batchSize);
        }
    }

    protected function getAlgoliaIndex(string $applicationId, string $apiKey, string $indexName) : SearchIndex
    {
        try {
            $client = SearchClient::create($applicationId, $apiKey);
            return $client->initIndex($indexName);
        } catch (\Exception $e) {
            $output->writeln('Unable to initalize Algolia client');
            throw $e;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Get and validate options
        $applicationId = $input->getOption(self::APPLICATION_ID);
        $apiKey = $input->getOption(self::API_KEY);
        $indexName = $input->getOption(self::INDEX_NAME);
        $inputFilename = $input->getOption(self::INPUT_FILENAME);
        $batchSize = (int)$input->getOption(self::BATCH_SIZE);

        // Input validation guard clauses
        if(empty($applicationId)) {
            throw new InvalidOptionException('Application ID is required');
        }
        if(empty($apiKey)) {
            throw new InvalidOptionException('API Key is required');
        }
        if(empty($indexName)) {
            throw new InvalidOptionException('Index Name is required');
        }
        if(empty($inputFilename)) {
            throw new InvalidOptionException('Input Filename is required');
        }

        $importData = $this->getImportData($inputFilename);

        $algoliaIndex = $this->getAlgoliaIndex($applicationId, $apiKey, $indexName);


        // We have valid params, data, and client. Let's import!
        $io->progressStart(count($importData));
        $this->importData(
            $importData,
            $batchSize,
            $algoliaIndex,
            function(int $progressCount) use ($io) {
                $io->progressAdvance($progressCount);
            },
            function(\Exception $e, array $batchData) use ($output) {
                //@todo: formatting
                $output->writeln("\n\nUnable to save batch to Algolia");
                $output->writeln("Data:");
                $output->writeln(print_r($batchData, true));

                throw new RuntimeException($e->getMessage());
            }
        );
        $io->progressFinish();

        return Command::SUCCESS;
    }
}
