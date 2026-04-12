<?php

declare(strict_types=1);

namespace BookTracker\Tests\Stub;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Domain\Repository\BookRepositoryInterface;

final class InMemoryBookRepository implements BookRepositoryInterface
{
	/** @var array<string, Book> */
	private array $books = [];

	public function getById(string $id): Book
	{
		if (!isset($this->books[$id]))
		{
			throw new BookNotFoundException(sprintf('Book "%s" not found.', $id));
		}

		return $this->books[$id];
	}

	/** @return array<Book> */
	public function getAll(): array
	{
		return array_values($this->books);
	}

	public function save(Book $book): void
	{
		$this->books[$book->getId()] = $book;
	}

	public function delete(string $id): void
	{
		if (!isset($this->books[$id]))
		{
			throw new BookNotFoundException(sprintf('Book "%s" not found.', $id));
		}

		unset($this->books[$id]);
	}

	public function existsByTitle(string $title): bool
	{
		foreach ($this->books as $book)
		{
			if ($book->getTitle() === $title)
			{
				return true;
			}
		}

		return false;
	}
}
