<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\FollowRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FollowController extends AbstractController
{

    #[Route('/follow/accept/{requestId}', name: 'app_accept_follow_request')]
#[IsGranted(
    attribute: new Expression('user === followRequest.followed'),
    subject: 'followRequest',
    message: "Cette demande ne vous appartient pas."
)]
public function acceptFollowRequest(int $requestId, EntityManagerInterface $entityManager): Response
{
    // Récupérer la demande de suivi
    $followRequest = $entityManager->getRepository(FollowRequest::class)->find($requestId);

    if (!$followRequest) {
        throw new \Exception("Follow request not found.");
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

    $this->addFlash('success', 'Vous suivez maintenant cet utilisateur.');
    return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente

    
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
        // Créer une demande de suivi (FollowRequest) si le compte est privé
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



}
