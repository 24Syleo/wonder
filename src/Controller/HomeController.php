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
                'id'      => '1',
                'title'   => 'Question une',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adip',
                'rating'  => 20,
                'author'  => [
                    'name'   => 'Jean Dupont',
                    'avatar' => 'https://randomuser.me/api/portraits/men/6.jpg'
                ],
                'nbrOfResponse' => 15
            ],
            [
                'id'      => '2',
                'title'   => 'Question deux',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adip, Lorem again',
                'rating'  => -15,
                'author'  => [
                    'name'   => 'Sylvie Vartant',
                    'avatar' => 'https://randomuser.me/api/portraits/women/6.jpg'
                ],
                'nbrOfResponse' => 150
            ],
            [
                'id'      => '3',
                'title'   => 'Question trois',
                'content' => 'Lorem ipsum dolores, bla bla bla bla bla ',
                'rating'  => 0,
                'author'  => [
                    'name'   => 'Julie Martin',
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
