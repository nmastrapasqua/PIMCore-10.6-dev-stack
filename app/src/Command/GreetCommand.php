<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GreetCommand extends Command
{
    protected static $defaultName = 'app:greet';

    protected function configure(): void
    {
        $this->setDescription('Un semplice job schedulato che scrive un saluto');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = date('Y-m-d H:i:s');
        $output->writeln(sprintf('[%s] Buongiorno! Questo è un job schedulato.', $now));

        return Command::SUCCESS;
    }
}
