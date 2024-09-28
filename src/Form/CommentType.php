<?php

namespace App\Form;

use App\Entity\News;
use App\Entity\User;
use App\Entity\Review;
use App\Entity\Comment;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('rating', HiddenType::class, [
            'attr' => ['class' => 'rating-input'],
        ])
        ->add('content', TextareaType::class, $this->getConfiguration("", "Ajouter un commentaire..."))
        // ->add('parent', HiddenType::class, [
        //     'mapped' => false, // Pour le gérer manuellement dans le contrôleur
        //     'required' => false,
        // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
