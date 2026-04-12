<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Book;

use BookTracker\Application\Exception\ValidationException;

final readonly class DeleteBookCommand
{
	public string $id;

	public function __construct(string $id)
	{
		if (trim($id) === '')
		{
			throw new ValidationException('Book id must not be empty.');
		}

		$this->id = $id;
	}
}
