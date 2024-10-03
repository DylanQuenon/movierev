<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Media;
use App\Entity\Collections;
use App\Form\CollectionType;
use App\Entity\CollectionsMedia;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CollectionsRepository;
use App\Repository\SubscriptionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CollectionsController extends AbstractController
{
    #[Route('/user/{slug}/collections', name: 'user_collections')]
    public function index(User $user, CollectionsRepository $repo, EntityManagerInterface $entityManager,SubscriptionRepository $followingRepo): Response
    {
        $currentUser = $this->getUser();
    
        // Si l'utilisateur connecté est le propriétaire des collections, afficher toutes les collections
        if ($currentUser && $currentUser->getId() === $user->getId()) {
            $allCollections = $repo->findBy(['user' => $user]);
        } else {
            // Sinon, n'afficher que les collections non privées
            $allCollections = $repo->findBy([
                'user' => $user,
                'isPrivate' => false,
            ]);
        }

        $isPrivate = $user->getIsPrivate() && $this->getUser() !== $user;
        $isFollowing = !$isPrivate || ($this->getUser() && $followingRepo->isFollowing($this->getUser(), $user));

    
        // Créer un tableau pour stocker un média aléatoire par collection
        $randomMediaByCollection = [];
    
        foreach ($allCollections as $collection) {
            $collectionsMediaRepo = $entityManager->getRepository(CollectionsMedia::class);
            $mediasInCollection = $collectionsMediaRepo->findBy(['collection' => $collection]);
    
            if (!empty($mediasInCollection)) {
                $randomKey = array_rand($mediasInCollection);
                $randomMediaByCollection[$collection->getId()] = $mediasInCollection[$randomKey]->getMedias();
            } else {
                $randomMediaByCollection[$collection->getId()] = null;
            }
        }
    
        // Créer le formulaire pour ajouter une collection si c'est le propriétaire connecté
        $form = $currentUser && $currentUser->getId() === $user->getId()
            ? $this->createForm(CollectionType::class, new Collections())
            : null;
    
        return $this->render('user/tab/collection.html.twig', [
            'collections' => $allCollections,
            'user' => $user,
            'myForm' => $form ? $form->createView() : null,
            'randomMediaByCollection' => $randomMediaByCollection,
            'isPrivate' => $isPrivate,
            'isFollowing' => $isFollowing,
        ]);
    }
    
    #[Route('/users/{slug}/collections/{id}', name: 'collection_show')]
    public function show(Request $request, Collections $collection, EntityManagerInterface $entityManager, string $slug ): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['slug' => $slug]);
        $form = $this->getUser() && $this->getUser()->getId() === $user->getId()
        ? $this->createForm(CollectionType::class, $collection)
        : null;

        $collectionsMedia = $entityManager->getRepository(CollectionsMedia::class)
        ->findBy(['collection' => $collection], ['position' => 'ASC']); // Trie par position
        return $this->render('collections/show.html.twig', [
            'collection' => $collection,
            'user' => $user,
            'collectionsMedia' => $collectionsMedia, // Passer la liste triée à la vue
            'myForm' => $form ? $form->createView() : null,
        ]);

    }
    #[Route('/collections/new', name: 'collection_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $collection = new Collections();
       

        $form = $this->createForm(CollectionType::class, $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collection->setUser($this->getUser());
            $entityManager->persist($collection);
            $entityManager->flush();

            
            $this->addFlash(
                'success', 
                "La collection <strong>".$collection->getName()."</strong> a bien été créée"
            );

            
            return $this->redirectToRoute('user_collections', [
                'slug' => $this->getUser()->getSlug(),
            ]);
        }

        return $this->render('collection/new.html.twig', [
            'myForm' => $form->createView(),
        ]);
    }

    #[Route('/collections/edit/{id}', name: 'collection_edit')]
    public function edit(Request $request, Collections $collection, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur a les droits de modifier cette collection
        if ($collection->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette collection.');
        }

        $form = $this->createForm(CollectionType::class, $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success', 
                "La collection <strong>".$collection->getName()."</strong> a bien été modifiée"
            );

            return $this->redirectToRoute('user_collections', [
                'slug' => $this->getUser()->getSlug(),
            ]);
        }

        return $this->render('collection/edit.html.twig', [
            'myForm' => $form->createView(),
            'collection' => $collection,
        ]);
    }
    #[Route('/collections/delete/{id}', name: 'collection_delete')]
    public function delete(Request $request, Collections $collection, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur a les droits de modifier cette collection
        if ($collection->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette collection.');
        }

        $entityManager->remove($collection);
        $entityManager->flush();
        $this->addFlash(
            "success",
            "La collection <strong>".$collection->getName()."</strong> a bien été supprimée"
        );

        return $this->redirectToRoute('user_collections', [
            'slug' => $this->getUser()->getSlug(),
        ]);
    }


    
    #[Route('/collections/add/{collectionId}/{mediaId}', name: 'add_media_to_collection', methods: ['POST'])]
    public function addMediaToCollection(int $collectionId, int $mediaId, EntityManagerInterface $entityManager, CollectionsRepository $repo, Request $request): Response
    {
        // Trouver la collection par ID
        $collection = $repo->find($collectionId);
    
        if (!$collection) {
            // Rediriger avec un message d'erreur si la collection n'est pas trouvée
            $this->addFlash('error', 'Collection not found');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }
    
        // Récupérer le média à ajouter
        $media = $entityManager->getRepository(Media::class)->find($mediaId);
    
        if (!$media) {
            // Rediriger avec un message d'erreur si le média n'est pas trouvé
            $this->addFlash('error', 'Media not found');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }
    
        // Vérifier si le média est déjà dans la collection via CollectionsMedia
        $existingEntry = $entityManager->getRepository(CollectionsMedia::class)->findOneBy([
            'collection' => $collection,
            'medias' => $media,
        ]);
    
        if ($existingEntry) {
            // Rediriger avec un message d'erreur si le média est déjà dans la collection
            $this->addFlash('error', 'Media already in collection');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }
    
        // Créer un nouvel objet CollectionsMedia
        $collectionsMedia = new CollectionsMedia();
        $collectionsMedia->setCollection($collection);
        $collectionsMedia->setMedias($media);
    
        // Récupérer l'ordre actuel des médias dans la collection
        $currentMediaCount = count($entityManager->getRepository(CollectionsMedia::class)->findBy(['collection' => $collection]));
        $collectionsMedia->setPosition($currentMediaCount + 1); // Assurez-vous d'avoir une méthode setPosition
    
        // Enregistrer le nouvel objet CollectionsMedia
        $entityManager->persist($collectionsMedia);
        $entityManager->flush();
    
        // Rediriger avec un message de succès après l'ajout
        $this->addFlash('success', 'Media added to collection');
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }

    #[Route('/collections/remove/{collectionId}/{mediaId}', name: 'remove_media_from_collection')]
    public function removeMediaFromCollection(int $collectionId, int $mediaId, EntityManagerInterface $entityManager, CollectionsRepository $repo, Request $request): Response
    {
        // Trouver la collection par ID
        $collection = $repo->find($collectionId);

        if (!$collection) {
            // Rediriger avec un message d'erreur si la collection n'est pas trouvée
            $this->addFlash('error', 'Collection Introuvable');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }

        // Récupérer le média à supprimer
        $media = $entityManager->getRepository(Media::class)->find($mediaId);

        if (!$media) {
            // Rediriger avec un message d'erreur si le média n'est pas trouvé
            $this->addFlash('error', 'Media introuvable');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }

        // Trouver l'entrée dans CollectionsMedia
        $collectionsMedia = $entityManager->getRepository(CollectionsMedia::class)->findOneBy([
            'collection' => $collection,
            'medias' => $media,
        ]);

        if (!$collectionsMedia) {
            // Rediriger avec un message d'erreur si le média n'est pas dans la collection
            $this->addFlash('error', 'Media introuvable dans la collection');
            return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
        }

        // Supprimer l'entrée dans CollectionsMedia
        $entityManager->remove($collectionsMedia);
        $entityManager->flush();

        // Rediriger avec un message de succès après la suppression
        $this->addFlash('success', 'Media retiré de la collection');
        return $this->redirect($request->headers->get('referer')); // Rediriger vers la page précédente
    }


    // src/Controller/CollectionController.php

#[Route('/collections/{id}/update-order', name: 'collection_update_order', methods: ['POST'])]
public function updateOrder(Request $request, EntityManagerInterface $entityManager, Collections $collection): Response
{
    // Vérifie si la requête est en JSON
    if ($request->getContentType() !== 'json') {
        return new JsonResponse(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }

    $data = json_decode($request->getContent(), true);
    $newOrder = $data['order'];

    // Met à jour l'ordre dans la base de données
    foreach ($newOrder as $position => $mediaId) {
        // Récupérer l'instance de CollectionsMedia
        $collectionsMedia = $entityManager->getRepository(CollectionsMedia::class)->findOneBy([
            'collection' => $collection,
            'medias' => $mediaId,
        ]);

        if ($collectionsMedia) {
            $collectionsMedia->setPosition($position);
            $entityManager->persist($collectionsMedia);
        }
    }

    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}

    
    
    


    
}
