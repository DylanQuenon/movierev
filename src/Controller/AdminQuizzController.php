<?php

namespace App\Controller;

use App\Entity\Quizz;
use App\Form\QuizzType;
use App\Entity\Question;
use App\Form\QuestionType;
use App\Service\PaginationService;
use App\Repository\QuizzRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminQuizzController extends AbstractController
{
    /**
     * Affiche les quizz
     *
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/quizz/{page<\d+>?1}', name: 'admin_quizz_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setDataSource(Quizz::class)->setPage($page)->setLimit(9)->setRoute('admin_genre_index');
        $quizz = $pagination->getData();
        return $this->render('admin/quizz/index.html.twig', [
            'pagination' => $pagination,
            'quizz' => $quizz,
        ]);
    }
    
    /**
     * Crée un quizz
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/quizz/add', name: 'admin_quizz_create')]
    public function create(EntityManagerInterface $manager, Request $request): Response
    {
        $quizz = new Quizz();
        $form = $this->createForm(QuizzType::class, $quizz);     
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
       
            $manager->persist($quizz);
            $manager->flush();
            $this->addFlash(
                'success',
                "Le quizz <strong>{$quizz->getTitle()}</strong> a bien été enregistré !"
            );
            return $this->redirectToRoute('admin_quizz_index');
        }

        return $this->render('admin/quizz/create.html.twig', [
            'myForm' => $form->createView()

            
        ]);
    }

    #[Route('/admin/quizz/{slug}/edit', name: 'admin_quizz_edit')]
    public function edit(Quizz $quizz, EntityManagerInterface $manager, Request $request): Response
    {
     
        $form = $this->createForm(QuizzType::class, $quizz);     
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $quizz->setSlug('');

       
            $manager->persist($quizz);
            $manager->flush();
            $this->addFlash(
                'success',
                "Le quizz <strong>{$quizz->getTitle()}</strong> a bien été modifié !"
            );
            return $this->redirectToRoute('admin_quizz_index');
        }

        return $this->render('admin/quizz/edit.html.twig', [
            'myForm' => $form->createView(),
            'quizz'=>$quizz

            
        ]);
    }

    #[Route('/admin/quizz/{slug}/delete', name: 'admin_quizz_delete')]
    public function delete(Quizz $quizz, EntityManagerInterface $manager, Request $request): Response
    {
   
            // Supprimer le quiz
            $manager->remove($quizz);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le quizz <strong>{$quizz->getTitle()}</strong> a bien été supprimé !"
            );
  

        return $this->redirectToRoute('admin_quizz_index');
    }



    

    /**
     * Montre le quizz
     *
     * @param Quizz $quizz
     * @return Response
     */
    #[Route('/admin/quizz/{slug}', name: 'admin_quizz_show')]
    public function show(Quizz $quizz): Response
    {
        return $this->render('admin/quizz/show.html.twig', [
            'quizz' => $quizz,
        ]);
    }

    /**
     * Ajouter une question
     *
     * @param Quizz $quizz
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/quizz/{slug}/question/add', name: 'admin_question_new')]
    public function question(Quizz $quizz, EntityManagerInterface $manager, Request $request): Response
    {
        if (count($quizz->getQuestions()) >= 10) {
            $this->addFlash(
                'error',
                "Le quizz <strong>{$quizz->getTitle()}</strong> a déjà 10 questions. Vous ne pouvez pas en ajouter davantage."
            );
            return $this->redirectToRoute('admin_quizz_show', ['slug' => $quizz->getSlug()]);
        }
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);     
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $correctAnswers = array_filter($question->getAnswers()->toArray(), function ($answer) {
                return $answer->isCorrect();
            });
    
            if (count($correctAnswers) !== 1) {
                $this->addFlash(
                    'error',
                    'Il doit y avoir exactement une seule bonne réponse par question.'
                );
                return $this->render('admin/quizz/newquestion.html.twig', [
                    'myForm' => $form->createView(),
                    'quizz' => $quizz
                ]);
            }
            $question->setQuizz($quizz);

            foreach($question->getAnswers() as $answer)
            {
                $answer->setQuestion($question);
                $manager->persist($answer);
            }

       
            $manager->persist($question);
            $manager->flush();
            $this->addFlash(
                'success',
                "La question <strong>{$question->getTitle()}</strong> a bien été enregistrée !"
            );
            return $this->redirectToRoute('admin_quizz_show', ['slug' => $quizz->getSlug()]);
        }

        return $this->render('admin/quizz/newquestion.html.twig', [
            'myForm' => $form->createView(),
            'quizz'=>$quizz

            
        ]);
    }

    /**
     * Modifie la question
     *
     * @param [type] $slug
     * @param [type] $id
     * @param QuizzRepository $quizzRepository
     * @param QuestionRepository $questionRepository
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/quizz/{slug}/question/{id}/edit', name: 'admin_question_edit')]
    public function editQuestion($slug, $id, QuizzRepository $quizzRepository, QuestionRepository $questionRepository, EntityManagerInterface $manager, Request $request): Response {
       
        $quizz = $quizzRepository->findOneBy(['slug' => $slug]);
        $question = $questionRepository->find($id);
    
      
        if (!$quizz) {
            throw $this->createNotFoundException("Le quizz n'existe pas.");
        }
        if (!$question || $question->getQuizz() !== $quizz) {
            throw $this->createNotFoundException("La question n'existe pas ou n'appartient pas à ce quizz.");
        }
    
        // Création du formulaire
        $form = $this->createForm(QuestionType::class, $question);     
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $correctAnswers = array_filter($question->getAnswers()->toArray(), function ($answer) {
                return $answer->isCorrect();
            });
    
            if (count($correctAnswers) !== 1) {
                $this->addFlash(
                    'error',
                    'Il doit y avoir exactement une seule bonne réponse par question.'
                );
                return $this->render('admin/quizz/newquestion.html.twig', [
                    'myForm' => $form->createView(),
                    'quizz' => $quizz
                ]);
            }
            foreach ($question->getAnswers() as $answer) {
                $answer->setQuestion($question); 
                $manager->persist($answer);
            }
    
            $manager->flush(); // Sauvegarder les modifications
    
            // Ajouter un message flash
            $this->addFlash(
                'success',
                "La question <strong>{$question->getTitle()}</strong> a bien été modifiée !"
            );
    
            // Rediriger vers la page du quizz
            return $this->redirectToRoute('admin_quizz_show', ['slug' => $quizz->getSlug()]);
        }
    
        // Rendu du formulaire d'édition
        return $this->render('admin/quizz/editquestion.html.twig', [
            'myForm' => $form->createView(),
            'quizz' => $quizz,
            'question' => $question
        ]);
    }

    /**
     * Effacer une question
     *
     * @param [type] $slug
     * @param [type] $id
     * @param QuizzRepository $quizzRepository
     * @param QuestionRepository $questionRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/quizz/{slug}/question/{id}/delete', name: 'admin_question_delete')]
    public function deleteQuestion($slug, $id, QuizzRepository $quizzRepository, QuestionRepository $questionRepository, EntityManagerInterface $manager): Response {
       
        $quizz = $quizzRepository->findOneBy(['slug' => $slug]);
        $question = $questionRepository->find($id);
    
        if (!$quizz) {
            throw $this->createNotFoundException("Le quizz n'existe pas.");
        }
        if (!$question || $question->getQuizz() !== $quizz) {
            throw $this->createNotFoundException("La question n'existe pas ou n'appartient pas à ce quizz.");
        }
    
        // Suppression de la question
        $manager->remove($question);
        $manager->flush();
    
        // Ajouter un message flash
        $this->addFlash(
            'success',
            "La question a été supprimée avec succès."
        );
    
        // Rediriger vers la page du quizz
        return $this->redirectToRoute('admin_quizz_show', ['slug' => $quizz->getSlug()]);
    }
    
}
