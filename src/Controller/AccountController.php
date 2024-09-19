<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\Mime\Email;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        $loginError = null;

        
        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            // l'ereur est due à la limitation de tentative de connexion
            $loginError = "Trop de tentatives de connexion. Réessayez plus tard";
        }
        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

    #[Route("/logout", name: "account_logout")]
    public function logout(): void
    {
    }


   #[Route("/register", name:"account_register")]
   public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, FileUploaderService $fileUploader, MailerInterface $mailer): Response
   {
       $user = new User();
       $form = $this->createForm(RegistrationType::class, $user);
       $form->handleRequest($request);

       // partie traitement du formulaire
       if($form->isSubmitted() && $form->isValid())
       {
           
           // gestion de l'image
           $file = $form['avatar']->getData();
   
           if($file){
               $imageName = $fileUploader->upload($file);
               $user->setAvatar($imageName);
           }

           // gestion de l'inscription dans la bdd
           $hash = $hasher->hashPassword($user, $user->getPassword());
           $user->setPassword($hash);

            $email = (new Email())
            ->from('contact@laligzz.dylanquenon.com')  
            ->to($user->getEmail()) 
            ->replyTo($user->getEmail())
            ->subject("Bienvenue chez Movierev")
            ->html('<p>See Twig integration for better HTML integration!</p>');
    

            $mailer->send($email);
           $manager->persist($user);
           $manager->flush();

       // Envoi de l'e-mail


           return $this->redirectToRoute('account_login');
       }


       return $this->render("account/registration.html.twig",[
           'myForm' => $form->createView()
       ]);
   }
}
