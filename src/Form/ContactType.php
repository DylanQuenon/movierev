<?php

namespace App\Form;

use App\Entity\Contact;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('firstName', TextType::class, $this->getConfiguration('Prénom', 'EX: Brad'))
            ->add('lastName', TextType::class, $this->getConfiguration('Nom', 'EX: Pitt'))
            ->add('mail', EmailType::class, $this->getConfiguration('Mail', 'EX: BradochePitt@gmail.com'))
            ->add('object', TextType::class, $this->getConfiguration('Sujet', 'EX:Suggestion...'))
            ->add('message', TextareaType::class, $this->getConfiguration('Message', 'EX:Salut, c’est Brad Pitt. J’ai une petite suggestion : ajoutez une section spéciale pour les films dans lesquels je joue. Vous savez, juste pour le plaisir de rendre les choses encore plus incroyables...'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
