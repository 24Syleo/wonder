<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(QuestionRepository $questionRepo): Response
    {
        // 'https://randomuser.me/api/portraits/men/6.jpg'
        $questions = $questionRepo->findAll();

        return $this->render('home/index.html.twig', [
            'questions' => $questions,
        ]);
    }
}
