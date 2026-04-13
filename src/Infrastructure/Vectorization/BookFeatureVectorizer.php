<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Vectorization;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Service\VectorizerInterface;
use BookTracker\Domain\ValueObject\BookVector;

final class BookFeatureVectorizer implements VectorizerInterface
{
	private const array CATEGORIES = [
		'fiction',
		'non-fiction',
		'science',
		'history',
		'philosophy',
		'biography',
		'tech',
		'fantasy',
		'mystery',
		'romance',
	];

	public function vectorize(Book $book): BookVector
	{
		$vector = [];

		$category = strtolower($book->getCategory());
		foreach (self::CATEGORIES as $known)
		{
			$vector[] = $category === $known ? 1.0 : 0.0;
		}

		$vector[] = $book->getComplexity() / 10.0;

		$vector[] = (float)(crc32($book->getAuthor()) & 0xFFFFFFFF) / (float)0xFFFFFFFF;

		return new BookVector($vector);
	}
}
