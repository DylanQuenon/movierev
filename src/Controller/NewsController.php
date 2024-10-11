<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Entity\Comment;
use App\Form\ReplyType;
use App\Entity\ImgModify;
use App\Form\CommentType;
use App\Form\NewsEditType;
use App\Form\ImgModifyMainType;
use App\Repository\NewsRepository;
use App\Service\PaginationService;
use App\Service\FileUploaderService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{


    /**
     * Effectue la recherche ajax pour prendre les news
     *
     * @param Request $request
     * @param NewsRepository $newsRepo
     * @return JsonResponse
     */
    #[Route('/news/search/ajax', name: 'news_search_ajax', methods: ['GET'])]
    public function searchAjax(Request $request, NewsRepository $newsRepo): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (empty($query)) {
            return new JsonResponse([]); // Renvoie un tableau vide si aucun terme
        }

        $results = $newsRepo->findByNewsTitle($query)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $jsonResults = array_map(function ($news) {
            return [
                'title' => $news->getTitle(),
                'author' => $news->getAuthor()->getFullName(),
                'slug' => $news->getSlug(),
            ];
        }, $results);

        return new JsonResponse($jsonResults);
    }

    /**
     * Récupère toutes les news
     *
     * @param Request $request
     * @param NewsRepository $repo
     * @param PaginatorInterface $paginator
     * @param integer $page
     * @return Response
     */
    #[Route('/news/{page<\d+>?1}', name: 'news_index')]
    public function index(Request $request, NewsRepository $repo, PaginatorInterface $paginator, int $page = 1): Response
    {
        // Récupère le statut et l'ordre depuis les paramètres GET
        $status = $request->query->get('status');
        $order = $request->query->get('order', 'newest'); // Valeur par défaut
    
        // Initialisation de la requête de base
        $queryBuilder = $repo->createQueryBuilder('n');
    
        // Si un statut est sélectionné, on ajoute une condition à la requête
        if ($status) {
            $queryBuilder->andWhere('n.status = :status')
                         ->setParameter('status', $status);
        }
    
        // Ajoute un tri en fonction de l'ordre choisi
        if ($order === 'oldest') {
            $queryBuilder->orderBy('n.createdAt', 'ASC');
        } elseif ($order === 'most_views') {
            $queryBuilder->orderBy('n.viewsCount', 'DESC');
        } else {
            $queryBuilder->orderBy('n.createdAt', 'DESC');
        }
    
        // Pagination avec KnpPaginator
        $news = $paginator->paginate(
            $queryBuilder, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );
    
        return $this->render('news/index.html.twig', [
            'news' => $news,
            'status' => $status, // Pour indiquer quel statut est sélectionné
            'order' => $order, // Passe l'ordre à la vue
        ]);
    }
    

    /**
     * Ajouter une actualité
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/news/add", name:"news_create")]
    #[IsGranted(
        attribute: new Expression('(is_granted("ROLE_REDACTEUR")) or is_granted("ROLE_ADMIN")'),
        message: "Vous n'avez pas l'autorisation de poster des actualités"
    )]
    public function create(Request $request, EntityManagerInterface $manager, FileUploaderService $fileUploader): Response
    {
        $news = new News(); // nouvel object
        $form = $this->createForm(NewsType::class, $news); // le formulaire 
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $file = $form['cover']->getData(); // on récupère la couverture
            if($file){
                $imageName = $fileUploader->upload($file); // on appelle le service d'upload
                $news->setCover($imageName);
            }
            $news->setAuthor($this->getUser()); // l'auteur c'est le user connecté
            $manager->persist($news);
            // j'envoie les persistances dans la base de données
            $manager->flush();

            $this->addFlash(
                'success', 
                "L'actualité <strong>".$news->getTitle()."</strong> a bien été enregistrée" // message de confirmation
            );

            return $this->redirectToRoute('news_show',[
                'slug' => $news->getSlug() // redirection vers la news
            ]);
        }

        return $this->render("news/add.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Récupère l'actualité individuelle
     *
     * @param string $slug
     * @param News $news
     * @param NewsRepository $newsRepository
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/news/{slug}", name:"news_show")]
    public function show(string $slug, News $news, NewsRepository $newsRepository, Request $request, NotificationService $notifService, EntityManagerInterface $manager): Response
    {
        $news->setViewsCount($news->getViewsCount() + 1); // augmente le nombre de vues de 1
        $manager->flush();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
        
            $comment->setNews($news)  // Associe la review au commentaire
                    ->setAuthor($this->getUser());  // Associe l'auteur

                    $notifService->addNotification(
                        'newsComment',
                        $this->getUser(),
                        $news->getAuthor(), // Utilisateur qui a commenté la review
                        null,
                        $comment,// Le commentaire lui-même
                        $news,
                    );
                 

            // Persiste le commentaire
            $manager->persist($comment);
            $manager->flush();
            $form = $this->createForm(CommentType::class);

            $this->addFlash('success', 'Votre commentaire a été pris en compte.');
        }
        $comments= $news->getComments();
        $topComment = null;
        if (count($comments) > 0) {
            $topComment = array_reduce($comments->toArray(), function ($carry, $item) {
                return ($carry === null || $item->getLikes()->count() > $carry->getLikes()->count()) ? $item : $carry;
            });
        }
        $replyForms = [];
        foreach ($comments as $comment) {
            if ($comment->getParent() === null) {
                $replyForm = $this->createForm(ReplyType::class, new Comment(), [
                    'parent' => $comment,
                ]);
                $replyForms[$comment->getId()] = $replyForm->createView();
            }
        }

        // Récupère les trois dernières actualités, excluant celle affichée
        $latestNews = $newsRepository->createQueryBuilder('n')
        ->where('n.id != :currentNewsId')
        ->setParameter('currentNewsId', $news->getId())
        ->orderBy('n.createdAt', 'DESC')
        ->setMaxResults(2)
        ->getQuery()
        ->getResult();

        

   
        return $this->render("news/show.html.twig", [
            'news' => $news,
            'latestNews' => $latestNews,
            'myForm' => $form->createView(),
            'replyForms' => $replyForms,
            'topComment' => $topComment,
        ]);
    }

    /**
     * Incrémente le compteur de partages pour une actualité donnée.
     *
     * @param string $slug
     * @param News $news
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/news/{slug}/incrementationsharecounts", name:"news_incrementation_sharing")]
    public function incrementShareCounts(string $slug, News $news, EntityManagerInterface $em, Request $request): JsonResponse
    {
    
        // Incrémentation du compteur de partages
        $news->setShareCount($news->getShareCount() + 1);
        $em->flush(); // Sauvegarde des changements dans la base de données

        return new JsonResponse(['success' => true, 'newShareCount' => $news->getShareCount()]);
    }

    
    /**
     * Modifier une actualité
     *
     * @param News $news
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/news/{slug}/edit", name: "news_edit")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_REDACTEUR")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["news"].getAuthor()'),
        message: "Cette actualité ne vous appartient pas, vous ne pouvez pas la modifier"
    )]
    public function edit(News $news, Request $request, EntityManagerInterface $manager): Response
    {
        $cover = $news->getCover();
        if(!empty($cover)){
            $news->setCover(
                new File($this->getParameter('uploads_directory').'/'.$news->getCover())
            );
        }
        $form = $this->createForm(NewsEditType::class, $news);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
   

            $news->setSlug('')
                ->setCover($cover);
            $manager->persist($news);
            $manager->flush();
    
            $this->addFlash(
                'success',
                "L'actualité <strong>".$news->getTitle()."</strong> a bien été modifiée"
            );
    
            return $this->redirectToRoute('news_show',[
                'slug' => $news->getSlug()
            ]);
        }
    
        return $this->render("news/edit.html.twig",[
            "news" => $news,
            "myForm" => $form->createView()
        ]);
    }


    /**
     * Modifie l'image de l'actualité
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param News $news
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/news/{slug}/imgmodify", name:"news_img")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_REDACTEUR")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["news"].getAuthor()'),
        message: "Vous ne pouvez pas modifier l'actualité"
    )]
    public function imgModify(Request $request, EntityManagerInterface $manager, News $news, FileUploaderService $fileUploader): Response
    {
        $imgModify = new ImgModify();
        $form = $this->createForm(ImgModifyMainType::class, $imgModify);
        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid())
        {
            if(!$news->getCover() || !empty($news->getCover()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$news->getCover());
            }
            // gestion de l'image
            $file = $form['newPicture']->getData();
            if($file){
                $imageName = $fileUploader->upload($file);
                $news->setCover($imageName);
            }
            $manager->persist($news);
            $manager->flush();

            $this->addFlash(
            'success',
            'La couverture a bien été modifiée'
            );

            return $this->redirectToRoute('news_show',[
            'slug' => $news->getSlug()
                
            ]);
        }

        return $this->render("news/imgModify.html.twig",[
            'myForm' => $form->createView(),
            'news' => $news    
        ]);
    }

      /**
     * Efface les news
     *
     * @param News $news
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/news/{slug}/delete", name: "news_delete")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_REDACTEUR")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["news"].getAuthor()'),
        message: "Cette actualité ne vous appartient pas, vous ne pouvez pas la supprimer"
    )]
    public function delete(News $news, EntityManagerInterface $manager): Response
    {
        if(!empty($news->getCover())) // on retire l'image
        {
            unlink($this->getParameter('uploads_directory').'/'.$news->getCover());
        }
      
        $this->addFlash(
            "success",
            "L'actualité <strong>".$news->getTitle()."</strong> a bien été supprimée"
        );
        $manager->remove($news); // on retire de la db
        $manager->flush();
        
        return $this->redirectToRoute('news_index');
    }
}
