<?php

namespace App\Command;

use App\Entity\InputList;
use App\ScrabbleGame;
use App\Repository\InputListRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:scrabble:regenerate',
    description: 'Add a short description for your command',
)]
class ScrabbleRegenerateCommand extends Command
{
    public function __construct(
        private InputListRepository $inputListRepository,
        private ScrabbleGame $scrabbleGame
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->inputListRepository->add($this->scrabbleGame->generateGame(7, $io), true);

        return Command::SUCCESS;
    }
}
