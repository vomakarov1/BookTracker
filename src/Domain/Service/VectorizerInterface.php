<?php

declare(strict_types=1);

namespace BookTracker\Domain\Service;

use BookTracker\Domain\Entity\Book;

interface VectorizerInterface
{
	/**
	 * @return array<float>
	 */
	public function vectorize(Book $book): array;
}
