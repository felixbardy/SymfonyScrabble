<?php

namespace App\Command;

use App\ScrabbleGame;
use App\Repository\InputListRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:scrabble:demo',
    description: 'scrabble game',
)]
class ScrabbleDemoCommand extends Command
{
    public function __construct(
        private ScrabbleGame $scrabbleGame,
        private InputListRepository $inputListRepository
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $input = $this->inputListRepository
                      ->findOneBy([], ['id' => 'DESC'])
                      ->getInputList();
        
        $timeStart = microtime(true);

        $rows = $this->scrabbleGame->generateSolution($input);

        $io->section('Lettres');
        $io->writeln($input);
        $io->section('Top 10');
        
        $io->table(['mot', 'value'], $rows);
        
        $timeEnd = microtime(true);

        $executionTime = ($timeEnd - $timeStart);

        $memory = memory_get_peak_usage(true) / 1024 / 1024;

        $io->section("Temps d'exécution (en s) / mémoire Mo");
        $io->writeln($executionTime);
        $io->writeln($memory);

        //  Meilleur temps du moment

        $io->section("Record temps d'exécution (en s)");
        $io->writeln('0.045671939849854');

        return Command::SUCCESS;
    }
}
