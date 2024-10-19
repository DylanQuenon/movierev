<?php

namespace App\Controller;

use DateTime;
use App\Form\ResetPasswordType;
use App\Form\PasswordRequestType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetController extends AbstractController
{
    /**
     * Mot de passe oublié
     *
     * @param Request $request
     * @param UserRepository $userRepo
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(Request $request, UserRepository $userRepo, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer): Response
    {
        $form = $this->createForm(PasswordRequestType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepo->loadUserByUsername($email);
    
            if ($user) {
                // Générer un jeton unique
                $resetToken = $tokenGenerator->generateToken();
            
                // Stocker le token, l'email et la date d'expiration dans la session
                $request->getSession()->set('reset_token', $resetToken);
                $request->getSession()->set('reset_email', $email);
                
                // Définir une date d'expiration (par exemple, 1 heure à partir de maintenant)
                $expirationDate = (new DateTime())->modify('+1 hour');
                $request->getSession()->set('reset_token_expiration', $expirationDate->format('Y-m-d H:i:s'));
            
                // Créer le lien de réinitialisation
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);
            
                // Envoi de l'email
                $emailMessage = (new Email())
                    ->from('contact@movierev.dylanquenon.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation du mot de passe')
                    ->html("<p>Cliquez sur le lien suivant pour réinitialiser votre mot de passe : <a href='{$resetUrl}'>Réinitialiser mon mot de passe</a></p>");
            
                $mailer->send($emailMessage);
            
                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe.');
            } else {
                $this->addFlash('error', 'Aucun utilisateur avec cette adresse mail.');
                return $this->redirectToRoute('app_forgot_password');
            }
    
            return $this->redirectToRoute('home');
        }
    
        return $this->render('password_forgot/index.html.twig', [
            'myForm' => $form->createView(),
        ]);
    }
    
    
    /**
     * Nouveau mdp
     *
     * @param Request $request
     * @param UserRepository $userRepo
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @param string $token
     * @return Response
     */
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserRepository $userRepo, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, string $token): Response
    {
        // Récupérer l'email et le token de la session
        $sessionToken = $request->getSession()->get('reset_token');
        $sessionEmail = $request->getSession()->get('reset_email');
        $expirationDate = $request->getSession()->get('reset_token_expiration');

        // Vérifie si le token correspond
        if ($sessionToken !== $token) {
            throw new AccessDeniedException('Token invalide ou utilisateur non trouvé.');
        }

                // Vérifier si le token est expiré
        if ($expirationDate && new DateTime() > new DateTime($expirationDate)) {
            $this->addFlash('error', 'Le lien de réinitialisation a expiré. Veuillez en demander un nouveau.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Trouver l'utilisateur par l'email
        $user = $userRepo->loadUserByUsername($sessionEmail);

        if (!$user) {
            throw new AccessDeniedException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            // Encoder le nouveau mot de passe
            $hash = $hasher->hashPassword($user, $newPassword);

            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            // Supprimer les données de session
            $request->getSession()->remove('reset_token');
            $request->getSession()->remove('reset_email');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('account_login'); // rediriger vers la page de connexion
        }

        return $this->render('password_forgot/reset.html.twig', [
            'myForm' => $form->createView(),
        ]);
    }

}
