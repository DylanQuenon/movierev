<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImgModifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupère le type d'image depuis les options du formulaire
        $imageType = $options['image_type'];

        // Utilise le type d'image pour personnaliser le libellé du champ
        $label = match ($imageType) {
            'poster' => 'Choisissez un nouveau poster',
            'cover' => 'Choisissez une nouvelle couverture',
            default => 'Choisissez une nouvelle image', // Valeur par défaut
        };

        $builder
            ->add('newPicture', FileType::class, [
                'label' => $label
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Ajoute une option pour le type d'image
            'image_type' => null,
        ]);
    }
}
