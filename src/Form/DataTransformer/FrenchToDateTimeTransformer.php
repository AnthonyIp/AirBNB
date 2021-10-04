<?php

namespace App\Form\DataTransformer;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class qui gère la transformation des champs startDate et endDate de BookingType
 */
class FrenchToDateTimeTransformer implements DataTransformerInterface
{
	public function transform($date)
	{
		if ($date === null) {
			return '';
		}
		return $date->format('d/m/Y');
	}

	public function reverseTransform($frenchDate)
	{
		// frenchDate = 21/09/1979
		if ($frenchDate === null) {
			// Exception
			throw new TransformationFailedException("Vous devez fournir une date!");
		}

		$date = DateTime::createFromFormat('d/m/Y', $frenchDate);
		if (!$date) {
			// Exception
			throw new TransformationFailedException("Le format de la date n'est pas le bon !");
		}
		return $date;
	}
}
