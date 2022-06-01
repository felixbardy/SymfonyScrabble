<?php

namespace App;

use App\Entity\InputList;

class GameGenerator
{

    private array $letters = [
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

    public function __construct(
        private string $projectDir
    )
    {}

    public function generateGame(int $charCount): InputList
    {
        $vowels = "aeiouy";
        $consonants = "zrtpqsdfghjklmwxcvbn";
        $vowelsSize = 6;
        $consonantsSize = 20;
        $randomString = '';
        for ($i = 0; $i < $charCount; $i+=1) {
            if (rand(0,1)) {
                $randomString .= $vowels[rand(0,$vowelsSize-1)];
            } else {
                $randomString .= $consonants[rand(0,$consonantsSize-1)];
            }
        }
        $inputList = new InputList();
        $inputList->setInputList($randomString);
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