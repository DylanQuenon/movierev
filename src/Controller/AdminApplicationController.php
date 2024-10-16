<?php

namespace App\Controller;

use App\Entity\Application;
use Symfony\Component\Mime\Email;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminApplicationController extends AbstractController
{
    /**
     * Voir les demandes
     *
     * @param ApplicationRepository $repo
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/applications/{page<\d+>?1}', name: 'admin_application_index')]
    public function index(ApplicationRepository $repo, PaginationService $pagination, int $page): Response
    {
        $pagination->setDataSource(Application::class)->setPage($page)->setLimit(9)->setRoute('admin_application_index');
        $applications = $pagination->getData();
        return $this->render('admin/application/index.html.twig', [
            'pagination' => $pagination,
            'applications' => $applications,
        ]);
    }


    /**
     * Voir une demande
     *
     * @param Application $application
     * @return Response
     */
    #[Route('/admin/application/{id}', name: 'admin_application_show')]
    public function show(Application $application): Response
    {
        return $this->render('admin/application/show.html.twig', [
            'application' => $application,
        ]);
    }

    /**
     * Accepter une demande
     *
     * @param Application $application
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/admin/application/{id}/accept', name: 'admin_application_accept')]
    public function accept(Application $application, EntityManagerInterface $manager, MailerInterface $mailer): Response
    {
        // Assigner le rôle
        $user = $application->getUser();
        $role = $application->getRole();
        $user->setRoles([$role === 'moderator' ? 'ROLE_MODERATEUR' : 'ROLE_REDACTEUR']);

        // Persister les modifications de l'utilisateur
        $manager->persist($user);
        // Envoyer un email de notification à l'utilisateur
        $email = (new Email())
            ->from('contact@movierev.dylanquenon.com') 
            ->to($user->getEmail())
            ->subject('Votre candidature a été acceptée')
            ->htmlTemplate('mail/applicationMail.html.twig') // Utilise le template
            ->context([
                'role' => ($role === 'moderator' ? 'modérateur' : 'rédacteur'), // Passer le rôle au template
            ]);

        $mailer->send($email);

        // Retirer la candidature
        $manager->remove($application);


        // Enregistrer les changements dans la base de données
        $manager->flush();

        // Rediriger vers la liste des candidatures avec un message de succès
        $this->addFlash('success', 'La candidature a été acceptée avec succès.');
        return $this->redirectToRoute('admin_application_index');
    }

    /**
     * Rejeter une demande
     *
     * @param Application $application
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/admin/application/{id}/reject', name: 'admin_application_reject')]
    public function reject(Application $application, EntityManagerInterface $manager, MailerInterface $mailer): Response
    {
        // Envoyer un email de notification à l'utilisateur
        $user = $application->getUser();
        $email = (new Email())
            ->from('contact@movierev.dylanquenon.com') // Remplace par ton adresse email
            ->to($user->getEmail())
            ->subject('Votre candidature')
            ->htmlTemplate('mail/rejectedMail.html.twig'); // Utilise le template

        $mailer->send($email);

        // Supprimer la candidature
        $manager->remove($application);
        $manager->flush();

        // Ajouter un message flash pour informer l'utilisateur
        $this->addFlash('error', 'La candidature a été rejetée avec succès et l\'utilisateur a été informé.');

        // Rediriger vers la liste des candidatures
        return $this->redirectToRoute('admin_application_index');
    }
    

}
