<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\FollowRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FollowController extends AbstractController
{

    #[Route('/follow/accept/{requestId}', name: 'app_accept_follow_request')]
public function acceptFollowRequest(int $requestId, EntityManagerInterface $entityManager, Request $request): Response
{
    // Récupérer la demande de suivi
    $followRequest = $entityManager->getRepository(FollowRequest::class)->find($requestId);

    if (!$followRequest) {
        throw new \Exception("Follow request not found.");
    }

        // Vérifier si l'utilisateur actuel est le destinataire de la demande de suivi
        $currentUser = $this->getUser(); // Récupérer l'utilisateur actuel
        if ($followRequest->getRequested()!==($currentUser)) {
            // Retourner une réponse 403 si l'utilisateur n'est pas autorisé
            throw $this->createAccessDeniedException("Vous n'avez pas la permission d'accepter cette demande de suivi.");
        }
  

    // Accepter la demande de suivi
    $followRequest->setAccepted(true);

    // Créer un nouvel abonnement
    $subscription = new Subscription();
    $subscription->setFollower($followRequest->getRequester());
    $subscription->setFollowed($followRequest->getRequested());

    $entityManager->persist($subscription);
    $entityManager->remove($followRequest); // Supprimer la demande de suivi après l'acceptation
    $entityManager->flush();

    $this->addFlash('success', 'Demande acceptée.');
    return $this->redirectToRoute('user_show', ['slug' => $followRequest->getRequested()->getSlug()]);

    
}

#[Route('/follow/decline/{requestId}', name: 'app_decline_follow_request')]
public function declineFollowRequest(int $requestId, EntityManagerInterface $entityManager, Request $request): Response
{
    // Récupérer la demande de suivi
    $followRequest = $entityManager->getRepository(FollowRequest::class)->find($requestId);

    if (!$followRequest) {
        throw new \Exception("Follow request not found.");
    }

    // Vérifier si l'utilisateur actuel est le destinataire de la demande de suivi
    $currentUser = $this->getUser(); // Récupérer l'utilisateur actuel
    if ($followRequest->getRequested() !== $currentUser) {
        // Retourner une réponse 403 si l'utilisateur n'est pas autorisé
        throw $this->createAccessDeniedException("Vous n'avez pas la permission de refuser cette demande de suivi.");
    }

    // Supprimer la demande de suivi
    $entityManager->remove($followRequest);
    $entityManager->flush();

    $this->addFlash('success', 'Demande refusée.');
    return $this->redirectToRoute('user_show', ['slug' => $followRequest->getRequested()->getSlug()]);
}

#[Route('/follow/{followerId}/{followedId}', name: 'app_follow')]
public function addSubscription(int $followerId, int $followedId, EntityManagerInterface $entityManager, Request $request): Response
{
    // Récupérer les utilisateurs par ID
    $follower = $entityManager->getRepository(User::class)->find($followerId);
    $followed = $entityManager->getRepository(User::class)->find($followedId);

    if (!$follower || !$followed) {
        $this->addFlash('error', "Utilisateur non trouvé.");
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }

    // Vérifier si l'utilisateur essaie de s'abonner à lui-même
    if ($follower->getId() === $followed->getId()) {
        $this->addFlash('error', "Vous ne pouvez pas vous abonner à vous-même.");
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }

    // Vérifier que le compte est public
    if ($followed->getIsPrivate()) {
        // Vérifier si une demande de suivi a déjà été envoyée
        $existingFollowRequest = $entityManager->getRepository(FollowRequest::class)
            ->findOneBy(['requester' => $follower, 'requested' => $followed, 'is_accepted' => false]);
    
        if ($existingFollowRequest) {
            $this->addFlash('error', 'Une demande de suivi est déjà en attente.');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }
    
        // Créer une nouvelle demande de suivi (FollowRequest)
        $followRequest = new FollowRequest();
        $followRequest->setRequester($follower);
        $followRequest->setRequested($followed);
        $followRequest->setAccepted(false); // En attente d'acceptation
    
        $entityManager->persist($followRequest);
        $entityManager->flush();
    
        $this->addFlash('success', 'Demande de suivi envoyée.');
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }
    
    // Vérifier si l'abonnement n'existe pas déjà
    $existingSubscription = $entityManager->getRepository(Subscription::class)
        ->findOneBy(['follower' => $follower, 'followed' => $followed]);

    if ($existingSubscription) {
        $this->addFlash('error', "Vous suivez déjà cet utilisateur.");
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }

    // Créer un nouvel abonnement
    $subscription = new Subscription();
    $subscription->setFollower($follower);
    $subscription->setFollowed($followed);

    $entityManager->persist($subscription);
    $entityManager->flush();

    $this->addFlash('success', 'Vous suivez maintenant cet utilisateur.');
    return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
}
#[Route('/unfollow/{followerId}/{followedId}', name: 'app_unfollow')]
public function removeSubscription(int $followerId, int $followedId, EntityManagerInterface $entityManager, Request $request): Response
{
    // Récupérer les utilisateurs par ID
    $follower = $entityManager->getRepository(User::class)->find($followerId);
    $followed = $entityManager->getRepository(User::class)->find($followedId);

    if (!$follower || !$followed) {
        $this->addFlash('error', "Utilisateur non trouvé.");
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }

    // Vérifier si l'abonnement existe
    $existingSubscription = $entityManager->getRepository(Subscription::class)
        ->findOneBy(['follower' => $follower, 'followed' => $followed]);

    if (!$existingSubscription) {
        $this->addFlash('error', "Vous ne suivez pas cet utilisateur.");
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }

    // Supprimer l'abonnement
    $entityManager->remove($existingSubscription);
    $entityManager->flush();

    $this->addFlash('success', "Vous avez cessé de suivre cet utilisateur.");
    return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
}

#[Route('/account/requests', name: 'follow_requests')]
#[IsGranted('ROLE_USER')]
public function followRequests(EntityManagerInterface $entityManager): Response
{
    // Récupérer l'utilisateur actuel
    $currentUser = $this->getUser();

    // Vérifier si l'utilisateur est connecté
    if (!$currentUser) {
        return $this->redirectToRoute('account_login'); // Rediriger vers la page de connexion
    }

    // Récupérer toutes les demandes de suivi pour l'utilisateur actuel
    $followRequests = $entityManager->getRepository(FollowRequest::class)
        ->findBy(['requested' => $currentUser, 'is_accepted' => false]);

 

    return $this->render('account/requests.html.twig', [
        'followRequests' => $followRequests,
    ]);
}


}
