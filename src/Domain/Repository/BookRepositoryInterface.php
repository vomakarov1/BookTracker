<?php

declare(strict_types=1);

namespace BookTracker\Domain\Repository;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\BookNotFoundException;

interface BookRepositoryInterface
{
	/**
	 * @throws BookNotFoundException
	 */
	public function getById(string $id): Book;

	/**
	 * @param array<string> $ids
	 * @return array<string, Book>
	 */
	public function getByIds(array $ids): array;

	/**
	 * @return array<Book>
	 */
	public function getAll(): array;

	public function save(Book $book): void;

	/**
	 * @throws BookNotFoundException
	 */
	public function delete(string $id): void;

	public function existsByTitle(string $title): bool;
}
