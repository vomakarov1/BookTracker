<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Service\Stub;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Service\VectorizerInterface;

final class StubVectorizer implements VectorizerInterface
{
	/** @param array<string, array<float>> $vectors */
	public function __construct(private readonly array $vectors)
	{
	}

	/** @return array<float> */
	public function vectorize(Book $book): array
	{
		return $this->vectors[$book->getId()] ?? [0.0, 0.0];
	}
}
