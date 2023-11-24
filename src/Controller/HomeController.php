<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {

        $questions = [
            [
                'title' => 'titre un',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adip',
                'rating' => 20,
                'author' => [
                    'name' => 'Jean Dupont',
                    'avatar' => 'https://randomuser.me/api/portraits/men/6.jpg'
                ],
                'nbrOfResponse' => 15
            ],
            [
                'title' => 'titre deux',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adip, Lorem again',
                'rating' => -15,
                'author' => [
                    'name' => 'Sylvie Vartant',
                    'avatar' => 'https://randomuser.me/api/portraits/women/6.jpg'
                ],
                'nbrOfResponse' => 150
            ],
            [
                'title' => 'titre trois',
                'content' => 'Lorem ipsum dolores, bla bla bla bla bla ',
                'rating' => 0,
                'author' => [
                    'name' => 'Julie Martin',
                    'avatar' => 'https://randomuser.me/api/portraits/women/38.jpg'
                ],
                'nbrOfResponse' => 2
            ]
        ];

        return $this->render('home/index.html.twig', [
            'questions' => $questions,
        ]);
    }
}
