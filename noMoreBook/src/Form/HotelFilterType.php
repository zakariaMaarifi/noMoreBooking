<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'Ville',
            ])
            ->add('date', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // No data_class because it's a filter form
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}