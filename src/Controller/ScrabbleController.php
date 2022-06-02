<?php

namespace App\Controller;

use App\Entity\InputList;
use App\Form\WordSubmissionFormType;
use App\ScrabbleGame;
use App\Repository\InputListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ScrabbleController extends AbstractController
{
    public function __construct(
        private Environment $twig,
        private InputListRepository $inputListRepository,
        private ScrabbleGame $scrabbleGame
    )
    {}
    
    #[Route('/', name: 'root')]
    public function redirectToIndex(Request $request): Response
    {
        return $this->redirectToRoute('scrabble');
    }

    #[Route('/scrabble', name: 'scrabble')]
    public function index(Request $request): Response
    {
        $input = '';
        // On récupère le dernier set de lettre de la base de données
        // (celui d'aujourd'hui)
        $inputList = $this->inputListRepository
                      ->findOneBy([], ['id' => 'DESC'])
        ;
        
        // S'il n'existe pas, on en génère un
        if (null === $inputList) {
            $inputList = $this->scrabbleGame->generateGame(7);
            $this->inputListRepository->add($inputList, true);
        }

        // On récupère le set de lettre préédent
        $previousInputList = $this->inputListRepository
                                  ->find($inputList->getId()-1)
        ;

        $words = [];
        $previousInput = '';
        // S'il n'existe pas, le set de mots affiché sera vide
        if (null !== $previousInputList) {
            $previousInput = $previousInputList->getInput();
            $words = $this->scrabbleGame->generateSolution($previousInput);
        }
            
        // On récupère le set de lettres
        $input = $inputList->getInput();

        // On récupère la session
        $session = $request->getSession();
        // Si la session n'existe pas, on la démarre
        if (!$session->isStarted()) {
            $session->start();
        }
    
        // Si le set de lettres a changé, vider les tentatives
        if ($input !== $session->get('scrabble_input', '')) {
            $session->set('tries', []);
            $session->set('scrabble_input', $input);
        }

        // On récupère la liste des tentatives
        $tries = $session->get('tries', []);

        // On récupère le formulaire
        $form = $this->createForm(WordSubmissionFormType::class);
        $form->handleRequest($request);

        // Si le formlaire est rempli et valide, on récupère le mot donné
        if ($form->isSubmitted() && $form->isValid()) {
            $word = $form->getData()['word'];
            // Si on a pas encore enregistré d'essais, initialiser le champ
            if (null === $session->get('tries')) {
                $session->set('tries', [
                    [$word, $this->scrabbleGame->computeScore($word,$input)]
                ]);
            }
            // Sinon, ajouter notre essai et trier le tableau
            else {
                $tries[] = [$word, $this->scrabbleGame->computeScore($word,$input)];
                usort($tries, fn ($try1, $try2) => $try2[1] - $try1[1]);
                $session->set('tries', $tries);
            }
        }

        $response = new Response(
            $this->twig->render('scrabble/index.html.twig',
            [
                'input' => str_split($input),
                'submit_word_form' => $form->createView(),
                'tries' => $tries,
                'previous_input' => str_split($previousInput),
                'previous_words' => $words
            ])
        );

        return $response;
    }

    private function checkWordValidity(string $word): bool
    {
        //TODO Vérifier la validité à l'aide du set de lettres dans la bdd
        return true;
    }
}
