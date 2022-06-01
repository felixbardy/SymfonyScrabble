<?php

namespace App\Controller;

use App\Form\WordSubmissionFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ScrabbleController extends AbstractController
{
    public function __construct(
        private Environment $twig
    )
    {}

    #[Route('/scrabble', name: 'scrabble')]
    public function index(Request $request): Response
    {
        $input = "abcefgjki";

        $form = $this->createForm(WordSubmissionFormType::class);

        $form->handleRequest($request);

        $word = $form->getData()['word'];

        $response = new Response(
            $this->twig->render('scrabble/index.html.twig',
            [
                'input' => str_split($input),
                'submit_word_form' => $form->createView()
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
