<?php

namespace App\Controller;

// src/Controller/QuizzController.php

use DateTime;
use App\Entity\User;
use App\Entity\Quizz;
use App\Entity\Question;
use App\Entity\UserScore;
use App\Repository\QuizzRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuizzController extends AbstractController
{
    /**
     * Montre le quizz
     *
     * @param Quizz $quizz
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/quiz/{slug}', name: 'quizz_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Quizz $quizz, EntityManagerInterface $em): Response
    {

          // Vérifier si le quiz a une date de fin et si celle-ci est dépassée
    $currentDate = new DateTime();
    if ($quizz->getEndDate() && $currentDate > $quizz->getEndDate()) {
        $this->addFlash('warning', 'Ce quiz est terminé et n\'est plus disponible.');
        return $this->redirectToRoute('account_index');
    }

    
    // Récupérer l'utilisateur actuellement connecté
    $user = $this->getUser();
    // Récupérer le quiz
    $quizz = $em->getRepository(Quizz::class)->find($quizz->getId());
    // Vérifier si l'utilisateur a déjà participé au quiz
    $participation = $em->getRepository(UserScore::class)->findOneBy([
        'user' => $user,
        'quizz' => $quizz,
    ]);
    if($participation)
    {
        $this->addFlash('info', 'Vous avez déjà participé à ce quiz.');
        return $this->redirectToRoute('account_index');
    }

    $questions = $quizz->getQuestions(); 


        return $this->render('quizz/show.html.twig', [
            'quizz' => $quizz,
            'questions' => $questions,
            'participation'=> $participation
        ]);
    }
    
    /**
     * Vérifie la réponse
     *
     * @param Request $request
     * @param QuestionRepository $quizzRepository
     * @return JsonResponse
     */
    #[Route('/check-answer', name: 'check-answer')]
    public function checkAnswer(Request $request, QuestionRepository $quizzRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $questionId = $data['questionId'];
        $answerId = $data['answerId'];

        // Récupérez la bonne réponse depuis votre base de données ou un service
        $correctAnswerId = $quizzRepository->getCorrectAnswerId($questionId); 

        // Vérifiez si la réponse est correcte
        $isCorrect = $answerId == $correctAnswerId;

        return new JsonResponse([
            'isCorrect' => $isCorrect,
            'correctAnswerId' => $correctAnswerId,
        ]);
    }

    /**
     * Sauver le score
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param QuizzRepository $quizzRepository
     * @return JsonResponse
     */
    #[Route('/save-score', name: 'save-score')]
    public function saveScore(Request $request, EntityManagerInterface $manager, QuizzRepository $quizzRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $score = $data['score'];
        $quizzId = $data['quizzId'];
        $userId = $data['userId'];
        $userScore = new UserScore;
        $userScore->setScore($score);

        $quizz = $quizzRepository->find($quizzId);
        if (!$quizz) {
            return $this->json(['status' => 'error', 'message' => 'Quizz not found'], 404);
        }

        $userScore->setQuizz($quizz);
        $userScore->setUser($this->getUser());
        $userScore->setScore($score);
        $manager->persist($userScore);
        $manager->flush();
        return $this->json(['status' => 'success']);
    }
    
    #[Route('/ranking-quizz', name: 'quizz')]
    public function globalLeaderboard(EntityManagerInterface $em): Response
    {
        // Récupérer tous les utilisateurs qui ont participé à au moins un quiz
        $users = $em->getRepository(User::class)->findAll();

        // Préparer un tableau pour stocker les scores cumulés
        $leaderboard = [];

        foreach ($users as $user) {
            // Récupérer toutes les participations de l'utilisateur
            $participations = $em->getRepository(UserScore::class)->findBy(['user' => $user]);

            // Calculer le score total de l'utilisateur
            $totalScore = array_sum(array_map(function($participation) {
                return $participation->getScore();
            }, $participations));

            // Ajouter l'utilisateur et son score cumulé au tableau du classement
            $leaderboard[] = [
                'user' => $user,
                'totalScore' => $totalScore,
            ];
        }

        // Trier le tableau par score total décroissant
        usort($leaderboard, function ($a, $b) {
            return $b['totalScore'] <=> $a['totalScore'];
        });

        // Renvoyer le classement à la vue
        return $this->render('quizz/ranking.html.twig', [
            'leaderboard' => $leaderboard,
        ]);
    }


}
