<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Book;

use BookTracker\Application\Exception\ValidationException;

final readonly class CreateBookCommand
{
	public string $title;
	public string $author;
	public string $category;
	public int $complexity;

	public function __construct(
		string $title,
		string $author,
		string $category,
		int $complexity,
	)
	{
		if (trim($title) === '')
		{
			throw new ValidationException('Book title must not be empty.');
		}

		if (trim($author) === '')
		{
			throw new ValidationException('Book author must not be empty.');
		}

		if (trim($category) === '')
		{
			throw new ValidationException('Book category must not be empty.');
		}

		if ($complexity < 1 || $complexity > 10)
		{
			throw new ValidationException(
				sprintf('Book complexity must be between 1 and 10, %d given.', $complexity)
			);
		}

		$this->title = $title;
		$this->author = $author;
		$this->category = $category;
		$this->complexity = $complexity;
	}
}
