<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ResetPasswordRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Length;
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
    public function resetPassword(RateLimiterFactory $passwordRecoveryLimiter,UserPasswordHasherInterface $userPasswordHasher,string $token, EntityManagerInterface $em, ResetPasswordRepository $resetPasswordRepo, Request $req)
    {
        $limiter = $passwordRecoveryLimiter->create($req->getClientIp());
        if (false === $limiter->consume(1)->isAccepted())
        {
            $this->addFlash('error', 'Vous avez fais trop de demande, patientez une heure pour recommencer');
            return $this->redirectToRoute('login');
        }
        
        $resetPassword = $resetPasswordRepo->findOneBy(['token' => sha1($token)]);
        if(!$resetPassword || $resetPassword->getExpiredAt() < new DateTime('now'))
        {
            if ($resetPassword)
            {
                $em->remove($resetPassword);
                $em->flush();
            }
            $this->addFlash('error', 'Votre demande est expiré.');
            return $this->redirectToRoute('login');
        }

        $passwordForm = $this->createFormBuilder()->add('password', PasswordType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez renseigner un mot de passe'
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit faire au moins 6 caractères'
                ])
            ]
        ])->getForm();

        $passwordForm->handleRequest($req);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid())
        {
            $passHasher = $passwordForm->get('password')->getData();
            $user       = $resetPassword->getUser();
            $user->setPassword($userPasswordHasher->hashPassword($user, $passHasher));
            $em->remove($resetPassword);
            $em->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié');
            return $this->redirectToRoute('login');   
        }

        return $this->render('security/reset_password.html.twig',[
            'form' => $passwordForm->createView()
        ]);
    }

    #[Route('/reset_password_request', name: 'reset_password_request')]
    public function reset_password_request(RateLimiterFactory $passwordRecoveryLimiter,Request $req, UserRepository $userRepo, ResetPasswordRepository $resetPasswordRepo, EntityManagerInterface $em, MailerInterface $mailer)
    {
        $limiter = $passwordRecoveryLimiter->create($req->getClientIp());
        if (false === $limiter->consume(1)->isAccepted())
        {
            $this->addFlash('error', 'Vous avez fais trop de demande, patientez une heure pour recommencer');
            return $this->redirectToRoute('login');
        }

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
            $user    = $userRepo->findOneBy(['email' => $emailTo]);
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
                $resetPassword->setToken(sha1($token));
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
