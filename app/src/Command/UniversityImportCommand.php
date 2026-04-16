<?php

/**
 * Per lanciare il comando nel container usa:
 *  docker compose exec php bin/console app:university:import
 */

namespace App\Command;

use Pimcore\Model\DataObject\University;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UniversityImportCommand extends Command
{
    protected static $defaultName = 'app:university:import';

    public function __construct(
        private HttpClientInterface $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Import universities from an API');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->client->request('GET', 'http://universities.hipolabs.com/search?country=United+Kingdom');
        $universities = $response->toArray();

        // Cartella dove salvare gli oggetti
        $parentId = 1; // root folder, cambia se vuoi una sottocartella

        $count = 0;

        foreach ($universities as $data) {
            // Genera una key univoca dal nome
            $key = Service::getValidKey($data['name'], 'object');

            // Controlla se esiste già
            $existing = University::getByPath('/'. $key);
            if ($existing) {
                $output->writeln(sprintf('SKIP (esiste già): %s', $data['name']));
                continue;
            }

            $university = new University();
            $university->setParentId($parentId);
            $university->setKey($key);
            $university->setPublished(true);

            // Campi semplici
            $university->setName($data['name']);
            $university->setCountry($data['alpha_two_code']);

            // Block "urls" — array di BlockElement
            $urlsBlock = [];
            if (!empty($data['web_pages'])) {
                foreach ($data['web_pages'] as $webPage) {
                    $urlsBlock[] = [
                        'url' => new BlockElement('url', 'input', $webPage)
                    ];
                }
            }
            $university->setUrls($urlsBlock);

            $university->save();
            $count++;
            $output->writeln(sprintf('OK: %s', $data['name']));
        }

        $output->writeln(sprintf("\nImportate %d università.", $count));

        return Command::SUCCESS;
    }
}
