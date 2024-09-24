<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Form\GenreType;
use App\Service\PaginationService;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminGenreController extends AbstractController
{

        /**
     * Récupérer tous les films
     *
     * @param MediaRepository $repo
     * @return Response
     */
    #[Route('/admin/genres/{page<\d+>?1}', name: 'admin_genre_index')]
    public function index(GenreRepository $repo, PaginationService $pagination, int $page): Response
    {
        $pagination->setDataSource(Genre::class)->setPage($page)->setLimit(9)->setRoute('admin_genre_index');
        $genres = $pagination->getData();
        return $this->render('admin/genre/index.html.twig', [
            'pagination' => $pagination,
            'genres' => $genres,
        ]);
    }
    
    /**
     * Ajouter un nouveau genre
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/genre/new', name: 'admin_genre_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($genre);
            $manager->flush();

            $this->addFlash('success', 'Le genre a été ajouté avec succès.');

            return $this->redirectToRoute('admin_genre_index');
        }

        return $this->render('admin/genre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifier un genre existant
     *
     * @param Genre $genre
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/genre/{id}/edit', name: 'admin_genre_edit')]
    public function edit(Genre $genre, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'Le genre a été modifié avec succès.');

            return $this->redirectToRoute('admin_genre_index');
        }

        return $this->render('admin/genre/edit.html.twig', [
            'form' => $form->createView(),
            'genre' => $genre,
        ]);
    }

    /**
     * Supprimer un genre
     *
     * @param Genre $genre
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/genre/{id}/delete', name: 'admin_genre_delete', methods: ['POST'])]
    public function delete(Genre $genre, EntityManagerInterface $manager, Request $request): Response
    {
        
            $manager->remove($genre);
            $manager->flush();

            $this->addFlash('success', 'Le genre a été supprimé avec succès.');
        

        return $this->redirectToRoute('admin_genre_index');
    }
    
 
}
