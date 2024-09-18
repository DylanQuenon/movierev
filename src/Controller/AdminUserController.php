<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use App\Form\SearchTeamType;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Affiche tous les users
     *
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/users/{page<\d+>?1}', name: 'admin_users_index')]
    public function index(Request $request, PaginationService $pagination, UserRepository $repo, int $page): Response
    {
        $pagination->setDataSource(User::class)->setPage($page)->setLimit(9)->setRoute('admin_users_index');
        $users = $pagination->getData();
        
    
         return $this->render('admin/user/index.html.twig', [
           'pagination' => $pagination,
           'users' => $users,
          
        ]);
    }
    /**
     * Permet de modifier uniquement le roles des users
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/users/{id}/edit", name: "admin_users_edit")]
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
      
        $avatar = $user->getAvatar();
        if(!empty($avatar)){
            $user->setAvatar(
                new File($this->getParameter('uploads_directory').'/'.$user->getAvatar())
            );
        }
        $form = $this->createFormBuilder($user)
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
                'Rédacteur'=> 'ROLE_REDACTEUR',
                'Modérateur'=> 'ROLE_MODERATEUR',
            ],
            'multiple' => true,
            'expanded' => true
        ])
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setAvatar($avatar);

         
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le roles de <strong>".$user->getFullName()."</strong> ont bien été modifiés"
            );
            return $this->redirectToRoute('admin_users_index',[
              
              ]);
            
        }

        return $this->render("admin/user/edit.html.twig",[
            "user" => $user,
            "myForm" => $form->createView()
        ]);

    }
 

}
