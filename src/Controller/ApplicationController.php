<?php

// src/Controller/ApplicationController.php
namespace App\Controller;

use App\Form\JobType;
use App\Entity\Application;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApplicationController extends AbstractController
{
    /**
     * Envoie une candidature pour devenir rédacteur ou modérateur
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserInterface $user
     * @return Response
     */
    
    #[Route('/postuler', name: 'app_apply')]
    #[IsGranted('ROLE_USER')]
    public function apply(Request $request, EntityManagerInterface $em, UserInterface $user): Response
    {
        $application = new Application();
        $application->setUser($user);


        $form = $this->createForm(JobType::class, $application);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($application);
            $em->flush();

            $this->addFlash('success', 'Votre candidature a été envoyée !');
            return $this->redirectToRoute('account_index');
        }

        return $this->render('application/apply.html.twig', [
            'myForm' => $form->createView(),
        ]);
    }
}

