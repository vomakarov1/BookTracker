<?php

declare(strict_types=1);

namespace BookTracker\Domain\Repository;

use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;

interface ReadingEntryRepositoryInterface
{
	/**
	 * @throws ReadingEntryNotFoundException
	 */
	public function getById(string $id): ReadingEntry;

	/**
	 * @return array<ReadingEntry>
	 */
	public function getByUserId(string $userId): array;

	/**
	 * @return array<ReadingEntry>
	 */
	public function getByBookId(string $bookId): array;

	public function save(ReadingEntry $entry): void;

	/**
	 * @throws ReadingEntryNotFoundException
	 */
	public function delete(string $id): void;

	public function existsByUserAndBook(string $userId, string $bookId): bool;

	public function nextId(): string;
}
