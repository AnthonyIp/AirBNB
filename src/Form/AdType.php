<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ApplicationType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdType extends ApplicationType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', TextType::class, $this->getConfiguration('Titre', 'Donner le titre de votre annonce'))
			->add('slug', TextType::class, $this->getConfiguration('Chaine URL', 'Adresse web (automatique)', ["required" => false]))
			->add('coverImage', UrlType::class, $this->getConfiguration('URL de l\'image principal', 'Donner l\'adresse d\'une image qui donne vraiment envie'))
			->add('introduction', TextType::class, $this->getConfiguration('Introduction', 'Donner une description globale de l\'annonce'))
			->add('content', TextareaType::class, $this->getConfiguration('Description detaillée', 'Donner une description detaillée de l\'annonce'))
			->add('rooms', IntegerType::class, $this->getConfiguration('Nombre de chambres', 'Donner le nombre de chambres disponibles'))
			->add('price', MoneyType::class, $this->getConfiguration('Prix par nuit', 'Indiquer le prix pour une nuit'))
			->add('images', CollectionType::class, [
				'entry_type'   => ImageType::class,
				'allow_add'    => true,
				'allow_delete' => true,
			]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
								   'data_class' => Ad::class,
							   ]);
	}
}
