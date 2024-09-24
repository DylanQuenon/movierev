<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\ActorType;
use App\Entity\ImgModify;
use App\Form\ActorEditType;
use App\Form\ImgModifyMainType;
use App\Service\PaginationService;
use App\Repository\ActorRepository;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminActorController extends AbstractController
{
    /**
     * Récupère les acteurs
     *
     * @param ActorRepository $repo
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/actors/{page<\d+>?1}', name: 'admin_actors_index')]
    public function index(ActorRepository $repo, PaginationService $pagination, int $page): Response
    {
        $pagination->setDataSource(Actor::class)->setPage($page)->setLimit(9)->setRoute('admin_actors_index');
        $actors = $pagination->getData();
        return $this->render('admin/actor/index.html.twig', [
            'pagination' => $pagination,
            'actors' => $actors,
        ]);
    }

    /**
     * Afficher un nouvel acteur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/admin/actors/new", name:"admin_actors_new")]
    public function create(Request $request, EntityManagerInterface $manager, FileUploaderService $fileUploader): Response
    {
        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);     
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $file = $form['picture']->getData();
            if($file){
                $imageName = $fileUploader->upload($file);
                $actor->setPicture($imageName);
            }

            // je persiste mon objet actor
            $manager->persist($actor);
            $manager->flush();
          
            $this->addFlash(
                'success', 
                "L'acteur <strong>".$actor->getName()."</strong> a bien été enregistré"
            );

            return $this->redirectToRoute('admin_actors_index',[
                'slug' => $actor->getSlug()
            ]);
        }

        return $this->render("admin/actor/new.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Modifier un acteur
     *
     * @param Actor $actor
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/actors/{slug}/edit", name: "admin_actors_edit")]
    public function edit(Actor $actor, Request $request, EntityManagerInterface $manager): Response
    {
        $picture = $actor->getPicture();
        if(!empty($picture)){
            $actor->setPicture(
                new File($this->getParameter('uploads_directory').'/'.$actor->getPicture())
            );
        }

        $form = $this->createForm(ActorEditType::class, $actor);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $actor
                ->setPicture($picture);
        
            $manager->persist($actor);
            $manager->flush();
    
            $this->addFlash(
                'success',
                "L'acteur <strong>".$actor->getFullName()."</strong> a bien été modifié"
            );
    
            return $this->redirectToRoute('admin_actors_index');
        }
    
        return $this->render("admin/actor/edit.html.twig",[
            "actor" => $actor,
            "myForm" => $form->createView(),
     
        ]);
    }

    /**
     * Modifier l'acteur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Actor $actor
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/admin/actors/{slug}/imgmodify", name:"admin_actors_img")]
    public function imgModify(Request $request, EntityManagerInterface $manager, Actor $actor, FileUploaderService $fileUploader): Response
    {
        $imgModify = new ImgModify();
        $form = $this->createForm(ImgModifyMainType::class, $imgModify);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            if(!$actor->getPicture() || !empty($actor->getPicture()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$actor->getPicture());
            }
                // gestion de l'image
                $file = $form['newPicture']->getData();
                if($file){
                    $imageName = $fileUploader->upload($file);
                    $actor->setPicture($imageName);
                }
                $manager->persist($actor);
                $manager->flush();

                $this->addFlash(
                'success',
                'La couverture a bien été modifiée'
                );

                return $this->redirectToRoute('admin_actors_index',[
                'slug' => $actor->getSlug()
                
            ]);
        }

        return $this->render("admin/actor/imgModify.html.twig",[
            'myForm' => $form->createView(),
            
        'actor' => $actor 
            
        ]);
    }

    /**
     * Effacer l'acteur
     *
     * @param Actor $actor
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/actors/{slug}/delete", name: "admin_actors_delete")]
    public function delete(Actor $actor, EntityManagerInterface $manager): Response
    {
        if(!empty($actor->getPicture()))
        {
            unlink($this->getParameter('uploads_directory').'/'.$actor->getPicture());
        }
        $this->addFlash(
            "success",
            "L'acteur <strong>".$actor->getFullName()."</strong> a bien été supprimé"
        );
        $manager->remove($actor);
        $manager->flush();
        
        return $this->redirectToRoute('admin_actors_index');
    }
}
