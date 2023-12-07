<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ResetPasswordRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'signup')]
    public function signup(Request $req, EntityManagerInterface $em, UserPasswordHasherInterface $passHasher, MailerInterface $mailer)
    { 
        $user     = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($req);

        if($userForm->isSubmitted() && $userForm->isValid())
        {
            $user->setPassword($passHasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Bienvenue sur Wonder!');
            $email = new TemplatedEmail();
            $email->to($user->getEmail())
                  ->subject('Bienvenue sur wonder')
                  ->htmlTemplate('@email_template/welcome.html.twig')
                  ->context([
                    'username' => $user->getFirstname()
                  ]);
            $mailer->send($email);
            return $this->redirectToRoute('login');
        }
        
        return $this->render('security/signup.html.twig', [
            'form' => $userForm->createView()
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser())
        {
            return $this->redirectToRoute('home');
        }

        $error    = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error'    => $error,
            'username' => $username
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
    }

    #[Route('/reset_password/{token}', name: 'reset_password')]
    public function resetPassword()
    {
        return $this->json([]);
    }

    #[Route('/reset_password_request', name: 'reset_password_request')]
    public function reset_password_request(Request $req, UserRepository $userRepo, ResetPasswordRepository $resetPasswordRepo, EntityManagerInterface $em, MailerInterface $mailer)
    {
        $emailForm = $this->createFormBuilder()->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez renseigner votre email'
                ])
            ]
        ])->getForm();

        $emailForm->handleRequest($req);

        if($emailForm->isSubmitted() && $emailForm->isValid())
        {
            $emailTo = $emailForm->get('email')->getData();
            $user = $userRepo->findOneBy(['email' => $emailTo]);
            if ($user) 
            {
                $oldResetPassword = $resetPasswordRepo->findOneBy(['user' => $user]);
                if ($oldResetPassword)
                {
                    $em->remove($oldResetPassword);
                    $em->flush();
                }
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setExpiredAt(new \DateTimeImmutable('+2 hours'));
                $token = substr(str_replace(['+','/','=',],'', base64_encode(random_bytes(30))),0,20);
                $resetPassword->setToken($token);
                $em->persist($resetPassword);
                $em->flush();
                $email = new TemplatedEmail();
                $email->to($emailTo)
                      ->subject('Demande de réinitialisation de mot de passe')
                      ->htmlTemplate('@email_template/reset_password_request.html.twig')
                      ->context([
                        'username' => $user->getFirstname(),
                        'token' => $token
                      ]);
                $mailer->send($email);
            }
            $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser le mot de passe');
            return $this->redirectToRoute('home');
        }

        return $this->render('security/reset_password_request.html.twig',[
            'form' => $emailForm->createView()
        ]);
    }
}
