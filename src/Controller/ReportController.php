<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Report;
use App\Entity\Review;
use App\Entity\Comment;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReportController extends AbstractController
{

    #[IsGranted('ROLE_MODERATEUR')]
    #[Route('/reports/{page<\d+>?1}', name: 'reports_index')]
    public function index(Request $request, ReportRepository $repo, PaginatorInterface $paginator, int $page = 1): Response
    {
        // Pagination avec KnpPaginator
        $reports= $paginator->paginate(
            $repo->findAll(), // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );
    
        return $this->render('report/index.html.twig', [
            'reports' => $reports,
        ]);
    }


    /**
     * Signaler un utilisateur, une review ou un commentaire
     */
    #[Route('/report/{type}/{id}', name: 'app_report', methods: ['POST'])]
    public function report(string $type, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reason = $request->request->get('reason');

        if (!$reason) {
            throw new BadRequestHttpException('La raison du signalement est requise.');
        }

        $report = new Report();
        $report->setReason($reason)
        ->setReportedBy($this->getUser());

        // Selon le type, associer le signalement à un commentaire ou à un utilisateur
        if ($type === 'review_comment') {
            $comment = $entityManager->getRepository(Comment::class)->find($id);
            if (!$comment) {
                throw $this->createNotFoundException('Commentaire non trouvé.');
            }
            $report->setComment($comment);
        } elseif ($type === 'review') {
            $review = $entityManager->getRepository(Review::class)->find($id);
            if (!$review) {
                throw $this->createNotFoundException('Review non trouvée.');
            }
            $report->setReview($review);
        } elseif ($type === 'reported_user') {
            $user = $entityManager->getRepository(User::class)->find($id);
            if (!$user) {
                throw $this->createNotFoundException('Utilisateur non trouvé.');
            }
            $report->setUser($user);
        } else {
            throw new BadRequestHttpException('Type de signalement invalide.');
        }

        $entityManager->persist($report);
        $entityManager->flush();

        $this->addFlash('success', 'Le signalement a été enregistré avec succès.');

        // Redirection vers la page précédente
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        // Si le referer n'est pas disponible, redirigez vers la page d'accueil ou une autre page par défaut
        return $this->redirectToRoute('app_home');
    }


    /**
     * Supprimer un signalement
     */
    #[Route('/report/delete/{id}', name: 'report_delete', methods: ['POST'])]
    public function deleteReport(int $id, EntityManagerInterface $entityManager): Response
    {
        $report = $entityManager->getRepository(Report::class)->find($id);

        if (!$report) {
            throw $this->createNotFoundException('Signalement non trouvé.');
        }

        $entityManager->remove($report);
        $entityManager->flush();

        $this->addFlash('success', 'Le signalement a été supprimé avec succès.');

        // Redirection vers la page précédente
        $referer = $this->requestStack->getCurrentRequest()->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home');
    }

}
