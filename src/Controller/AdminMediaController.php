<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Form\SearchsType;
use App\Form\ImgModifyType;
use App\Form\MediaEditType;
use App\Service\PaginationService;
use App\Repository\MediaRepository;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminMediaController extends AbstractController
{
    /**
     * Récupérer tous les films
     *
     * @param MediaRepository $repo
     * @return Response
     */
    #[Route('/admin/medias/{page<\d+>?1}', name: 'admin_medias_index')]
    public function index(MediaRepository $repo, PaginationService $pagination, Request $request, int $page): Response
    {
        $form = $this->createForm(SearchsType::class);
        $form->handleRequest($request);
        $isSubmited=false;

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            $isSubmited=true;
            $medias = $repo->searchMediabyName($query);
        }else{
            $pagination->setDataSource(Media::class)->setPage($page)->setLimit(9)->setRoute('admin_medias_index');
            $medias = $pagination->getData();

        }
        return $this->render('admin/media/index.html.twig', [
            'pagination' => $pagination,
            'medias' => $medias,
            'searchForm' => $form->createView(),
            'isSubmitted'=>$isSubmited
        ]);
    }

    /**
     * Ajouter un média dans l'admin
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/admin/medias/new", name:"admin_medias_new")]
    public function create(Request $request, EntityManagerInterface $manager, FileUploaderService $fileUploader): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media);     
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

        
            $file = $form['cover']->getData();
            if($file){
                $imageName = $fileUploader->upload($file);
                $media->setCover($imageName);
            }

            $file2 = $form['poster']->getData();
            if($file2){
                $imageName = $fileUploader->upload($file2);
                $media->setPoster($imageName);
            }
           
            foreach($media->getCastings() as $casting)
            {
                $casting->setMedia($media);
                $manager->persist($casting);
            }

            // je persiste mon objet media
            $manager->persist($media);
            $manager->flush();
          
            

            $this->addFlash(
                'success', 
                "Le Media <strong>".$media->getTitle()."</strong> a bien été enregistré"
            );

            return $this->redirectToRoute('admin_medias_index',[
                'slug' => $media->getSlug()
            ]);
        }

        return $this->render("admin/media/new.html.twig",[
            'myForm' => $form->createView()
        ]);
    }


    /**
     * Permet de modifier un média
     *
     * @param Media $media
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/medias/{slug}/edit", name: "admin_medias_edit")]
    public function edit(Media $media, Request $request, EntityManagerInterface $manager): Response
    {

        $oldCoverPath = $this->getOldImagePath($media, 'cover');
        $oldPosterPath = $this->getOldImagePath($media, 'poster');
       
        $poster = $media->getPoster();
        if(!empty($poster)){
            $media->setPoster(
                new File($this->getParameter('uploads_directory').'/'.$media->getPoster())
            );
        }
        $cover = $media->getCover();
        if(!empty($cover)){
            $media->setCover(
                new File($this->getParameter('uploads_directory').'/'.$media->getCover())
            );
        }

        $form = $this->createForm(MediaEditType::class, $media);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $media
            ->setPoster($poster)
            ->setCover($cover);
            
                        foreach($media->getCastings() as $casting)
                        {
                            $casting->setMedia($media);
                            $manager->persist($casting);
                        }
            

            $manager->persist($media);
            $manager->flush();
    
            $this->addFlash(
                'success',
                "Le Média <strong>".$media->getTitle()."</strong> a bien été modifiée"
            );
    
            return $this->redirectToRoute('admin_medias_index');
        }
    
        return $this->render("admin/media/edit.html.twig",[
            "media" => $media,
            "myForm" => $form->createView(),
            // "oldLogoPath" => $oldLogoPath,
            // "oldLogoBackgroundPath" => $oldLogoBackgroundPath,
            "oldCoverPath" => $oldCoverPath,
            "oldPosterPath" => $oldPosterPath,
            // "oldNewsPicturePath" => $oldNewsPicturePath,
        ]);
    }
    
    /**
     * Récupère l'ancienne imagege d'un média
    *
    * @param Media $media
    * @param string $type
    * @return string|null
    */
    private function getOldImagePath(Media $media, string $type): ?string
    {
        switch ($type) {
            case 'poster':
                return $media->getPoster();
            case 'cover':
                return $media->getCover();

            default:
                return null;
        }
    }

  
    /**
     * Effacer un média
     *
     * @param Media $media
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/medias/{slug}/delete", name: "admin_medias_delete")]
    public function delete(Media $media, EntityManagerInterface $manager): Response
    {

        if(!empty($media->getPoster()))
        {
            unlink($this->getParameter('uploads_directory').'/'.$media->getPoster());
        }
     
        if(!empty($media->getCover()))
        {
            unlink($this->getParameter('uploads_directory').'/'.$media->getCover());
        }
        $this->addFlash(
            "success",
            "Le media <strong>".$media->getTitle()."</strong> a bien été supprimé"
        );
        $manager->remove($media);
        $manager->flush();
        
        return $this->redirectToRoute('admin_medias_index');
    }


   
    /**
     * Modifier la couverture
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Media $media
     * @return Response
     */
    #[Route("/admin/medias/{slug}/imgModify", name:"mediaImgModify")]
    public function imgModify(Request $request, EntityManagerInterface $manager, Media $media): Response
    {
        
        $type = $request->query->get('type');
        switch ($type) {
            case 'poster':
                $form = $this->createForm(ImgModifyType::class, null, ['image_type' => 'poster']);
                break;
            case 'cover':
                $form = $this->createForm(ImgModifyType::class, null, ['image_type' => 'cover']);
                break;
            default:
                throw $this->createNotFoundException('Type d\'image non valide.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = $data['newPicture'];

            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $e->getMessage();
                }

                $oldImagePath = $this->getOldImagePath($media, $type);
                if ($oldImagePath && file_exists($this->getParameter('uploads_directory') . '/' . $oldImagePath)) {
                    unlink($this->getParameter('uploads_directory') . '/' . $oldImagePath);
                }

                // Modifie l'image appropriée en fonction du type d'image sélectionné
                switch ($type) {
                    case 'poster':
                        $media->setPoster($newFilename);
                        break;
                    case 'cover':
                        $media->setCover($newFilename);
                        break;
                }

                $manager->persist($media);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'L\'image a bien été modifiée'
                );

                return $this->redirectToRoute('admin_medias_index');
            }
        }

        return $this->render("admin/media/imgModify.html.twig",[
            'myForm' => $form->createView(),
            'mediaName' => $media->getTitle(),
            'imageType' => $type,
            'oldImagePath' => $this->getOldImagePath($media, $type), // Ajoutez cette ligne pour passer le chemin de l'ancienne image
        ]);
        
    }

}
