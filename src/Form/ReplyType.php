<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReplyType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, $this->getConfiguration("Réponse:", 'Ecrivez votre réponse...'))
            ->add('parent', HiddenType::class, [
                'data' => $options['parent'] ? $options['parent']->getId() : null,
                'mapped' => false,
                'required'=> false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'parent' => null,
        ]);
    }
}

