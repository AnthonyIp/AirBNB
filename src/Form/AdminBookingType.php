<?php

namespace App\Form;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\User;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminBookingType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, $this->getConfiguration("Date d'arrivée", "La date a laquelle vous comptez arriver.", ['widget' => 'single_text']))
            ->add('endDate', DateType::class, $this->getConfiguration("Date départ","La date a laquelle vous quittez les lieux.", ['widget' => 'single_text']))
            ->add('comment', TextareaType::class, $this->getConfiguration("Commentaire", "Commentaire du visiteur"))
            ->add('booker', EntityType::class, [
                'label' => "Client",
                'class' => User::class,
                'choice_label' => function ($user) {
                    return $user->getFirstName() . ' ' . strtoupper($user->getLastName());
                },
            ])
            ->add('ad', EntityType::class, [
                'label' => "Annonce",
                'class' => Ad::class,
                'choice_label' => 'title'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
