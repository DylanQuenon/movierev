<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Comment;
use App\Form\ReplyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{

    #[Route('/comments/{id}/like', name: 'comments_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(Request $request, Comment $comment, EntityManagerInterface $manager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
    
        $like = $manager->getRepository(Likes::class)->findOneBy(['author' => $user, 'comment' => $comment]);
    
        if ($like) {
            // Si l'utilisateur a déjà aimé, on le retire (dislike)
            $manager->remove($like);
            $action = 'disliked';
        } else {
            // Sinon, on ajoute un nouveau like
            $like = new Likes();
            $like->setAuthor($user);
            $like->setComment($comment);
            $manager->persist($like);
            $action = 'liked';
        }
    
        $manager->flush();
    
        // Calculer le nouveau top commentaire
        $topComment = $this->getTopComment($manager);
    
        return new JsonResponse([
            'action' => $action,
            'likesCount' => $comment->getLikes()->count(),
            'topComment' => $topComment ? [
                'id' => $topComment->getId(),
                'content' => $topComment->getContent(),
                'likesCount' => $topComment->getLikes()->count(),
            ] : null,
        ]);
    }
    
    // Fonction pour obtenir le top commentaire
    private function getTopComment(EntityManagerInterface $manager)
    {
        $comments = $manager->getRepository(Comment::class)->findAll();
        $topComment = null;
        $maxLikes = 0;
    
        foreach ($comments as $comment) {
            $likesCount = $comment->getLikes()->count();
            if ($likesCount > $maxLikes) {
                $maxLikes = $likesCount;
                $topComment = $comment;
            }
        }
    
        return $topComment;
    }
    
    #[Route("/comments/reply/{id}", name: "comment_reply", methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reply(Comment $comment, Request $request, EntityManagerInterface $manager): Response {
        $user = $this->getUser();
        $review = $comment->getReview();
        $news = $comment->getNews();
    
        $reply = new Comment();
        $form = $this->createForm(ReplyType::class, $reply, [
            'parent' => $comment,
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setReview($review)
                  ->setAuthor($user)
                  ->setParent($comment);
    
            $manager->persist($reply);
            $manager->flush();
    
            $this->addFlash('success', 'Reply posted');
            if ($review) {
                return $this->redirectToRoute('reviews_show', ['slug' => $review->getSlug()]);
            } else {
                return $this->redirectToRoute('news_show', ['slug' => $news->getSlug()]);
            }
        } else {
            $this->addFlash('danger', 'There was an error posting your reply.');
        }
    
        if ($review) {
            return $this->redirectToRoute('reviews_show', ['slug' => $review->getSlug()]);
        } else {
            return $this->redirectToRoute('news_show', ['slug' => $news->getSlug()]);
        }
    }
}
