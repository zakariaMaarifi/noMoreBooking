<?php
namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hotelName', TextType::class, [
            'label' => 'Nom de l\'hôtel',
            'mapped' => false,
        ])
        ->add('hotelCity', TextType::class, [
            'label' => 'Ville de l\'hôtel',
            'mapped' => false,
        ])
            ->add('startDate')
            ->add('endDate')
            ->add('price')
            ->add('isAvailable');
    }
}
