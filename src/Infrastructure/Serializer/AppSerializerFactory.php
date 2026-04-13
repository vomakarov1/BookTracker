<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Serializer;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class AppSerializerFactory
{
	public static function create(): SerializerInterface
	{
		$extractor = new PropertyInfoExtractor(
			typeExtractors: [new ReflectionExtractor()],
		);

		return new Serializer(
			normalizers: [
				new ArrayDenormalizer(),
				new ObjectNormalizer(propertyTypeExtractor: $extractor),
			],
			encoders: [
				new JsonEncoder(),
				new CsvEncoder(),
			],
		);
	}
}
