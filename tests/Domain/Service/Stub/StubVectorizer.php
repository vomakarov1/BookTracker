<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Service\Stub;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Service\VectorizerInterface;
use BookTracker\Domain\ValueObject\BookVector;

final class StubVectorizer implements VectorizerInterface
{
	/** @param array<string, array<float>> $vectors */
	public function __construct(private readonly array $vectors)
	{
	}

	public function vectorize(Book $book): BookVector
	{
		return new BookVector($this->vectors[$book->getId()] ?? [0.0, 0.0]);
	}
}
