<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Media;
use App\Entity\Review;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReviewsType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration('Titre', 'Entrez le titre de la news'))
            ->add('rating', HiddenType::class, [
                'attr' => ['class' => 'rating-input'],
            ])
            ->add('content', TextareaType::class, $this->getConfiguration("Votre review", "Ecrivez votre review..."))
            // ->add('createdAt', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('Media', EntityType::class, [
            //     'class' => Media::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('author', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
