<?php

declare(strict_types=1);

namespace BookTracker\Domain\Entity;

use BookTracker\Domain\Exception\InvalidBookException;
use BookTracker\Domain\ValueObject\BookComplexity;

final class Book
{
	public function __construct(
		private readonly string $id,
		private readonly string $title,
		private readonly string $author,
		private readonly string $category,
		private readonly BookComplexity $complexity,
	)
	{
		if (trim($title) === '')
		{
			throw new InvalidBookException('Book title must not be empty.');
		}

		if (trim($author) === '')
		{
			throw new InvalidBookException('Book author must not be empty.');
		}
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getAuthor(): string
	{
		return $this->author;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public function getComplexity(): int
	{
		return $this->complexity->getValue();
	}
}
