<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\QuestionRepository;
use App\Repository\VoteRepository;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function userProfile(User $user): Response
    {
        $currentUser = $this->getUser();

        if($currentUser === $user){
            return $this->redirectToRoute('current_user');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user', name: 'current_user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function currentUserProfile(Uploader $uploader,Request $req, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user     = $this->getUser();
        $userForm = $this->createForm(UserType::class, $user);

        $userForm->remove('password');
        $userForm->add('newPassword', PasswordType::class, ['label' => 'Nouveau mot de passe', 'required' => false]);
        $userForm->handleRequest($req);
        if($userForm->isSubmitted() && $userForm->isValid())
        {
            $newPassword = $user->getNewPassword();
            if ($newPassword)
            {
                $hash = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hash);
            }
            $picture = $userForm->get('pictureFile')->getData();
            if ($picture) {
                $user->setPicture($uploader->uploadProfileImage($picture, $user->getPicture()));
            }
            $em->flush();
            $this->addFlash('success', 'Modification utilisateur réussie');
            return $this->redirectToRoute('home');
        }

        return $this->render('user/index.html.twig', [
            'form' => $userForm->createView()
        ]);
    }

    #[Route('/user/questions/list', name: 'questions_user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function questionUserProfile(QuestionRepository $questionRepo): Response
    {
        $user      = $this->getUser();
        $questions = $questionRepo->findBy(['author'  => $user ]);
        return $this->render('user/question.html.twig', [
            'user'      => $user,
            'questions' => $questions
        ]);
    }

    #[Route('/user/comments/list', name: 'comments_user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function commentUserProfile(CommentRepository $commentRepo): Response
    {
        $user     = $this->getUser();
        $comments = $commentRepo->findBy(['author' => $user]);
        return $this->render('user/comment.html.twig', [
            'user'     => $user,
            'comments' => $comments
        ]);
    }
}
