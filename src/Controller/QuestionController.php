<?php

namespace App\Controller;

use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request): Response
    {

        $formQuestion = $this->createForm(QuestionType::class);

        $formQuestion->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion->createView(),
        ]);
    }

    #[Route('/question/{id}', name: 'question_show')]
    public function show(Request $request, string $id): Response
    {
        $question =  [
            'title' => 'Question une',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adip',
            'rating' => 20,
            'author' => [
                'name' => 'Jean Dupont',
                'avatar' => 'https://randomuser.me/api/portraits/men/6.jpg'
            ],
            'nbrOfResponse' => 15
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }
}
