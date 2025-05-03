<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Categorie;
use App\Entity\Livres;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class LivresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 4,  // 'max' ensures the value must be shorter than 5 characters
                        'maxMessage' => 'Le titre doit comporter moins de 5 caractères.'
                    ])
                ]
            ])
            ->add('isbn')
            ->add('slug')
            ->add('image')
            ->add('resume')
            ->add('editeur')
            ->add('dateEdition', null, [
                'widget' => 'single_text'
            ])
            ->add('prix')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
'choice_label' => 'id',])
            ->add("Save", SubmitType::class, [
                'label' => 'Save Changes'  // Optional label customization
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livres::class,
        ]);
    }
}
