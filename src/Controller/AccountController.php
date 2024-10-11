<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DeleteType;
use App\Entity\ImgModify;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\ImgModifyMainType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Mime\Email;
use App\Service\FileUploaderService;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    /**
     * Actions de connexion
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
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

    /**
     * Déconnexion
     *
     * @return void
     */
    #[Route("/logout", name: "account_logout")]
    public function logout(): void
    {
    }


    /**
     * Inscription
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @param FileUploaderService $fileUploader
     * @param MailerInterface $mailer
     * @return Response
     */
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
            ->from('contact@movierev.dylanquenon.com')  
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

   /**
    * Permet de modifier son profil
    *
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
   #[Route("/account/profile", name:"account_profile")]
   #[IsGranted('ROLE_USER')]
   public function profile(Request $request, EntityManagerInterface $manager): Response
   {
       $user = $this->getUser(); // permet de récup l'utilisateur connecté
       $originalAvatar = $user->getAvatar(); // Conserve le nom de l'avatar actuel

       
       $fileName = $user->getAvatar();
       if (!empty($originalAvatar) && file_exists($this->getParameter('uploads_directory').'/'.$originalAvatar)) {
        $user->setAvatar(new File($this->getParameter('uploads_directory').'/'.$originalAvatar));
       }

       $form = $this->createForm(AccountType::class,$user);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid())
       {

           $user->setSlug('')
                ->setAvatar($fileName);

           $manager->persist($user);
           $manager->flush();

           $this->addFlash(
               'success',
               "Les données ont été enregistrées avec succés"
           );

           return $this->redirectToRoute('account_index');
       }

       return $this->render("account/profile.html.twig",[
           'myForm' => $form->createView(),
           'user'=>$user,
           'avatar' => $originalAvatar
       ]);

   }

   #[Route("/account/password-update", name:"account_password")]
   #[IsGranted('ROLE_USER')]
   public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
   {
       $passwordUpdate = new PasswordUpdate();
       $user = $this->getUser();
       $originalAvatar = $this->getUser()->getAvatar();
       $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid())
       {
           // vérifier si le mot de passe correspond à l'ancien
           if(!password_verify($passwordUpdate->getOldPassword(),$user->getPassword()))
           {
               // gestion de l'erreur
               $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
           }else{
               $newPassword = $passwordUpdate->getNewPassword();
               $hash = $hasher->hashPassword($user, $newPassword);

               $user->setPassword($hash);
               $manager->persist($user);
               $manager->flush();

               $this->addFlash(
                   'success',
                   'Votre mot de passe a bien été modifié'
               );

               return $this->redirectToRoute('account_index');
           }

       }

       return $this->render("account/password.html.twig", [
           'myForm' => $form->createView(),
           'avatar'=> $originalAvatar
       ]);

   }

   /**
    * Permet dde supprimer l'image de profil
    *
    * @param EntityManagerInterface $manager
    * @return Response
    */
   #[Route("/account/delimg", name:"account_delimg")]
   #[IsGranted('ROLE_USER')]
   public function removeImg(EntityManagerInterface $manager): Response
   {
       $user = $this->getUser();
       if(!empty($user->getAvatar()))
       {
           unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
           $user->setAvatar('');
           $manager->persist($user);
           $manager->flush();
           $this->addFlash(
               'success',
               'Votre avatar a bien été supprimé'
           );
       }

     
       return $this->redirectToRoute('account_index');

   }
   /**
    * Permet de modifier l'image de profil
    *
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
   #[Route("/account/imgmodify", name:"account_modifimg")]
   #[IsGranted('ROLE_USER')]
   public function imgModify(Request $request, EntityManagerInterface $manager): Response
   {
       $imgModify = new ImgModify();
       $user = $this->getUser();
       $avatar= $user->getAvatar();
       $form = $this->createForm(ImgModifyMainType::class, $imgModify);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid())
       {
           //permet de supprimer l'image dans le dossier
           // gestion de la non-obligation de l'image
           if(!empty($user->getAvatar()))
           {
               unlink($this->getParameter('uploads_directory').'/'.$user->getAvatar());
           }

             // gestion de l'image
             $file = $form['newPicture']->getData();
             if(!empty($file))
             {
                 $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                 $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                 $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                 try{
                     $file->move(
                         $this->getParameter('uploads_directory'),
                         $newFilename
                     );
                 }catch(FileException $e)
                 {
                     return $e->getMessage();
                 }
                 $user->setAvatar($newFilename);
             }
             $manager->persist($user);
             $manager->flush();

             $this->addFlash(
               'success',
               'Votre avatar a bien été modifié'
             );

             return $this->redirectToRoute('account_index');

       }

       return $this->render("account/imgModify.html.twig",[
           'myForm' => $form->createView(),
           'avatar'=> $avatar
       ]);
   }

   /**
    * Permet de supprimer le compte
    *
    * @param Request $request
    * @param UserPasswordHasherInterface $hasher
    * @param EntityManagerInterface $manager
    * @param TokenStorageInterface $tokenStorage
    * @return Response
    */
   #[Route("/account/delete", name: "account_delete")]
   #[IsGranted('ROLE_USER')]
   public function deleteAccount(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage): Response
   {
       $user = $this->getUser();
       $form = $this->createForm(DeleteType::class);

       //si pas d'user redierction vers login
       if (!$user) {

           $this->addFlash(
               'danger',
               'Vous devez être connecté.'
           );
           return $this->redirectToRoute('account_login');
       }

       $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();
           $submittedEmail = $data['email'];
           $submittedPassword = $data['password'];

           //adresse mail dans db?
           if ($user->getEmail() === $submittedEmail) {
               $isPasswordValid = $hasher->isPasswordValid($user, $submittedPassword);

               //password valide ? 
               if ($isPasswordValid) {

                   $avatarFilename = $user->getAvatar();

                   //avatar deleted
                   if ($avatarFilename) {
                       $avatarFilePath = $this->getParameter('uploads_directory') . '/' . $avatarFilename;
                       if (file_exists($avatarFilePath)) {
                           unlink($avatarFilePath);
                       }
                   }

                   //effacer la connexion
                   $tokenStorage->setToken(null);
                   //retirer
                   $manager->remove($user);
                   $manager->flush();

                   $this->addFlash(
                       'success',
                       'Compte supprimé avec succès'
                   );

                   return $this->redirectToRoute('home');
               }
           }

           $this->addFlash(
               'danger',
               'Email ou mot de passe incorrect'
           );
       }

       return $this->render('account/delete.html.twig', [
           'myForm' => $form->createView()
       ]);
   }

}
