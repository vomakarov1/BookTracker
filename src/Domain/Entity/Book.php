<?php

declare(strict_types=1);

namespace BookTracker\Domain\Entity;

use InvalidArgumentException;

final class Book
{
	public function __construct(
		private readonly string $id,
		private readonly string $title,
		private readonly string $author,
		private readonly string $category,
		private readonly int $complexity,
	)
	{
		if (trim($title) === '')
		{
			throw new InvalidArgumentException('Book title must not be empty.');
		}

		if (trim($author) === '')
		{
			throw new InvalidArgumentException('Book author must not be empty.');
		}

		if ($complexity < 1 || $complexity > 10)
		{
			throw new InvalidArgumentException(
				sprintf('Book complexity must be between 1 and 10, %d given.', $complexity)
			);
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
		return $this->complexity;
	}
}
