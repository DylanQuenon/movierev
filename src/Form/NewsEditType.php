<?php

namespace App\Form;

use App\Entity\News;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class NewsEditType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration('Titre', 'Entrez le titre de la news'))
            ->add('content', TextareaType::class, $this->getConfiguration('Contenu', 'Entrez le contenu de l\'actualité'))
                    ->add('status', ChoiceType::class, [
                        'choices' => [
                            'Officiel' => 'officiel',
                            'Rumeur' => 'rumeur',
                            'Recommandations' => 'recommandations',
                            'Box Office' => 'box office'
                        ],
                        'label' => 'Statut',
                        'placeholder' => 'Choisissez un statut'
                    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
