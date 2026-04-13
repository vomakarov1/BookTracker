<?php

declare(strict_types=1);

namespace BookTracker\Domain\Service;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\ValueObject\BookVector;

interface VectorizerInterface
{
	public function vectorize(Book $book): BookVector;
}
