<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends ApplicationType
{

    private $transformer;

    public function __construct(FrenchToDateTimeTransformer $transformer){
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('startDate', TextType::class,
                $this->getConfiguration("Date d'arrivée",
                    "La date a laquelle vous comptez arriver."
                ))
            ->add('endDate', TextType::class,
                $this->getConfiguration("Date de départ",
                    "La date a laquelle vous quittez les lieux."
                ))
            ->add('comment', TextareaType::class,
                $this->getConfiguration(false,
                    "Si vous avez un commentaire, n'hesitez pas a en faire part!",
                    ["required" => false]
                ));
        $builder->get('startDate')->addModelTransformer($this->transformer);
        $builder->get('endDate')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
