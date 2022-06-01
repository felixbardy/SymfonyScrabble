<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:scrabble',
    description: 'scrabble game',
)]
class ScrabbleCommand extends Command
{
    public function __construct(private string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $mots = file($this->projectDir . '/data/mots.txt', FILE_IGNORE_NEW_LINES);

        $letters = [
            'a' => 1,
            'b' => 3,
            'c' => 3,
            'd' => 2,
            'e' => 1,
            'f' => 4,
            'g' => 2,
            'h' => 4,
            'i' => 1,
            'j' => 8,
            'k' => 5,
            'l' => 1,
            'm' => 3,
            'n' => 1,
            'o' => 1,
            'p' => 3,
            'q' => 10,
            'r' => 1,
            's' => 1,
            't' => 1,
            'u' => 1,
            'v' => 4,
            'w' => 4,
            'x' => 10,
            'y' => 4,
            'z' => 8,
            '-' => 0,
        ];

        $timeStart = microtime(true);

        $input = 'teaucre';

        // start here

        //exécution: ~52ms (dont ~49ms en filtrage)
        
        // 1• Filtrage
        $filtrage_start = microtime(true);

        $input_list = str_split($input);

        // Définition du filtre
        $est_faisable = function ($mot) use ($input_list) {
            $taille_mot = strlen($mot);
            // Si le mot a plus de lettres que notre input, on s'épargne les calculs
            if ($taille_mot > count($input_list)) return false;

            // Sinon, on vérifie qu'il est faisable
            for($i = 0; $i < $taille_mot; $i+=1) {
                $lettre = $mot[$i];
                //Pour chaque lettre du mot, on la recherche dans les données
                $key = array_search($lettre, $input_list);
                // Si on ne trouve pas la lettre, le mot n'est pas faisable
                if ($key === false) return false;

                // La lettre fait partie des données, on l'en enlève:
                // elle est utilisée
                unset($input_list[$key]);
            }
            // Si toutes les lettres sont contenues, le mot est faisable
            return true;
        };
        // On applique le filtre à notre liste de mots
        $mots = array_filter($mots, $est_faisable);

        $calcul_start = $filtrage_end = microtime(true);

        // 2• Calcul des scores
        // Fonction d'accumultaion du score
        $accumulate_score = function ($carry, $lettre) use ($letters) {
            return $carry + $letters[$lettre];
        };

        // Génération de la table des scores
        $rows = array_map(
            fn ($mot) => [$mot, array_reduce(str_split($mot), $accumulate_score, 0)],
            $mots
        );

        $tri_start = $calcul_end = microtime(true);

        // 3• Tri et coupe
        // Tri de la table par score
        usort($rows, fn ($row1, $row2) => $row2[1] - $row1[1]);
        // On ne garde que les 10 premiers
        $rows = array_slice($rows, 0, 10);

        $aff_start = $tri_end = microtime(true);
        
        
        $io->section('Lettres');
        $io->writeln($input);
        $io->section('Top 10');
        
        $io->table(['mot', 'value'], $rows);
        
        $aff_end = $timeEnd = microtime(true);

        $executionTime = ($timeEnd - $timeStart);

        $memory = memory_get_peak_usage(true) / 1024 / 1024;

        $io->section("Temps d'exécution (en s) / mémoire Mo");
        $io->writeln($executionTime);
        $io->writeln($memory);
        $io->note(sprintf('%f secondes en filtrage', $filtrage_end - $filtrage_start));
        $io->note(sprintf('%f secondes en calcul', $calcul_end - $calcul_start));
        $io->note(sprintf('%f secondes en tri', $tri_end - $tri_start));
        $io->note(sprintf('%f secondes en affichage', $aff_end - $aff_start));

        //  Meilleur temps du moment

        $io->section("Record temps d'exécution (en s)");
        $io->writeln('0.045671939849854');

        return Command::SUCCESS;
    }
}
