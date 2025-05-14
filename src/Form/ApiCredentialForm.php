<?php

namespace App\Form;

use App\Entity\ApiCredential;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiCredentialForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class,[
                'attr' => ['class' => 'form-control', 'placeholder'=>"L'url de la clé"],
                'label' => "L'adresse URL de l'API"
            ])
            ->add('apikey', TextType::class,[
                'attr' => ['class' => 'form-control', 'placeholder'=>"La clé de l'API"],
                'label' => "La clé de l'API"
            ])
            ->add('status', CheckboxType::class,[
                'attr' => ['class' => 'form-check-input'],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApiCredential::class,
        ]);
    }
}
