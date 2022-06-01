<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        $input = "abcefgjki";

        $response = new Response(
            $this->twig->render('scrabble/index.html.twig',
            [
                'input' => str_split($input),
                'submit_word_form' => "Un magnifique formulaire que voilà"
            ])
        );

        return $response;
    }
}
