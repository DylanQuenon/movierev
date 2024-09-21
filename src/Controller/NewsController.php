<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use App\Form\NewsEditType;
use App\Repository\NewsRepository;
use App\Service\PaginationService;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
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


    #[Route('/news/search/ajax', name: 'news_search_ajax', methods: ['GET'])]
    /**
     * Effectue la recherche
     *
     * @param Request $request
     * @param NewsRepository $newsRepo
     * @return JsonResponse
     */
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
    #[Route('/news/{page<\d+>?1}', name: 'news_index')]
    public function index(Request $request, NewsRepository $repo, PaginationService $pagination, int $page): Response
    {
    
        $pagination->setDataSource(News::class)->setPage($page)->setLimit(8)->setRoute('news_index');
        $news = $pagination->getData();

        return $this->render('news/index.html.twig', [
            'pagination' => $pagination,
            'news' => $news,
            
        ]);
    }

    #[Route("/news/add", name:"news_create")]
    #[IsGranted(
        attribute: new Expression('(is_granted("ROLE_REDACTEUR")) or is_granted("ROLE_ADMIN")'),
        message: "Vous n'avez pas l'autorisation de poster des actualités"
    )]
    public function create(Request $request, EntityManagerInterface $manager, FileUploaderService $fileUploader): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);     
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $file = $form['cover']->getData();
            if($file){
                $imageName = $fileUploader->upload($file);
                $news->setCover($imageName);
            }
            $news->setAuthor($this->getUser());
            // je persiste mon objet team
            $manager->persist($news);
            // j'envoie les persistances dans la bdd
            $manager->flush();

            $this->addFlash(
                'success', 
                "L'actualité <strong>".$news->getTitle()."</strong> a bien été enregistrée"
            );

            // return $this->redirectToRoute('news_show',[
            //     'slug' => $news->getSlug()
            // ]);
        }

        return $this->render("news/add.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    #[Route("/news/{slug}", name:"news_show")]
    public function show(string $slug, News $news, NewsRepository $newsRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $news->setViewsCount($news->getViewsCount() + 1);
        $manager->flush();

           // Récupère les trois dernières actualités, excluant celle affichée
    $latestNews = $newsRepository->createQueryBuilder('n')
    ->where('n.id != :currentNewsId')
    ->setParameter('currentNewsId', $news->getId())
    ->orderBy('n.createdAt', 'DESC')
    ->setMaxResults(3)
    ->getQuery()
    ->getResult();

   
        return $this->render("news/show.html.twig", [
            'news' => $news,
            'latestNews' => $latestNews,
        ]);
    }

    #[Route("/news/{slug}/incrementationsharecounts", name:"news_incrementation_sharing")]
    public function incrementShareCounts(string $slug, News $news, EntityManagerInterface $em, Request $request): JsonResponse
    {
    
        // Incrémentation du compteur de partages
        $news->setShareCount($news->getShareCount() + 1);
        $em->flush(); // Sauvegarde des changements dans la base de données

        return new JsonResponse(['success' => true, 'newShareCount' => $news->getShareCount()]);
    }

    #[Route("/news/{slug}/edit", name: "news_edit")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN") or is_granted("ROLE_REDACTEUR")'),
        subject: new Expression('args["news"].getAuthor()'),
        message: "Cette actualité ne vous appartient pas, vous ne pouvez pas la modifier"
    )]
    public function edit(News $news, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(NewsEditType::class, $news);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $news->setSlug('');
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
    


}
