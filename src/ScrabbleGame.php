<?php

namespace App;

use App\Entity\InputList;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScrabbleGame
{

    private static array $letters = [
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

    private static array $normalizeChars = array(
        'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
        'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
        'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
        'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
        'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
        'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
        'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
        'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
    );

    public function __construct(
        private string $projectDir
    )
    {}

    public function generateGame(int $charCount, SymfonyStyle $io = null): InputList
    {
        $mots = file($this->projectDir . '/data/mots.txt', FILE_IGNORE_NEW_LINES);

        $mots = array_filter($mots, fn ($mot) => strlen($mot) >= 7);

        $word = $mots[array_rand($mots)];

        if ($io) {
            $io->success(sprintf(
                'Le mot "%s" a été tiré aléatoirement parmi %d mots', 
                $word, count($mots)
            ));
        }
        
        $word = strtr($word,$this::$normalizeChars);

        $word = str_split($word);
        shuffle($word);
        $word = array_reduce($word, fn ($string, $letter) => $string . $letter, '');
        

        $inputList = new InputList();
        $inputList->setInputList($word);
        return $inputList;
    }

    public function computeScore(string $word, string $input) : int
    {
        $score = 0;
        foreach (str_split($word) as $letter) {
            $score += $this->letters[$letter];
        }
        return $score;
    }

    public function isValid(string $word, string $input) : bool
    {
        $taille_mot = strlen($word);
        $input_list = str_split($input);
        // Si le mot a plus de lettres que notre input, on s'épargne les calculs
        if ($taille_mot > count($input_list)) return false;

        // Sinon, on vérifie qu'il est faisable
        for($i = 0; $i < $taille_mot; $i+=1) {
            $lettre = $word[$i];
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
    }

    public function generateSolution(string $input) : array
    {
        $mots = file($this->projectDir . '/data/mots.txt', FILE_IGNORE_NEW_LINES);

        // start here

        //exécution: ~52ms (dont ~49ms en filtrage)
        
        // 1• Filtrage

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
        $accumulate_score = function ($carry, $lettre) {
            return $carry + $this->letters[$lettre];
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

        return $rows;
    }
}