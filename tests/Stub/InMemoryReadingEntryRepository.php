<?php

declare(strict_types=1);

namespace BookTracker\Tests\Stub;

use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;

final class InMemoryReadingEntryRepository implements ReadingEntryRepositoryInterface
{
	/** @var array<string, ReadingEntry> */
	private array $entries = [];

	public function getById(string $id): ReadingEntry
	{
		if (!isset($this->entries[$id]))
		{
			throw new ReadingEntryNotFoundException(sprintf('ReadingEntry "%s" not found.', $id));
		}

		return $this->entries[$id];
	}

	/** @return array<ReadingEntry> */
	public function getByUserId(string $userId): array
	{
		return array_values(
			array_filter(
				$this->entries,
				static fn(ReadingEntry $e): bool => $e->getUserId() === $userId,
			),
		);
	}

	/** @return array<ReadingEntry> */
	public function getByBookId(string $bookId): array
	{
		return array_values(
			array_filter(
				$this->entries,
				static fn(ReadingEntry $e): bool => $e->getBookId() === $bookId,
			),
		);
	}

	public function save(ReadingEntry $entry): void
	{
		$this->entries[$entry->getId()] = $entry;
	}

	public function delete(string $id): void
	{
		if (!isset($this->entries[$id]))
		{
			throw new ReadingEntryNotFoundException(sprintf('ReadingEntry "%s" not found.', $id));
		}

		unset($this->entries[$id]);
	}

	public function existsByUserAndBook(string $userId, string $bookId): bool
	{
		foreach ($this->entries as $entry)
		{
			if ($entry->getUserId() === $userId && $entry->getBookId() === $bookId)
			{
				return true;
			}
		}

		return false;
	}
}
