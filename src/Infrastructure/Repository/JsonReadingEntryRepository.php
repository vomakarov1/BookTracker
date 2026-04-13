<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Repository;

use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use BookTracker\Infrastructure\Storage\JsonFileStorage;
use DateMalformedStringException;
use DateTimeImmutable;
use JsonException;


final class JsonReadingEntryRepository implements ReadingEntryRepositoryInterface
{
	public function __construct(private readonly JsonFileStorage $storage)
	{
	}

	/**
	 * @param array<string, mixed> $row
	 * @throws DateMalformedStringException
	 */
	private function hydrate(array $row): ReadingEntry
	{
		$ratingValue = $row['rating'];
		$rating = ($ratingValue !== null) ? new ReadingEntryRating((int)$ratingValue) : null;

		$finishedAtValue = $row['finishedAt'];
		$finishedAt = ($finishedAtValue !== null) ? new DateTimeImmutable((string)$finishedAtValue) : null;

		return ReadingEntry::reconstruct(
			id: (string)$row['id'],
			userId: (string)$row['userId'],
			bookId: (string)$row['bookId'],
			status: ReadingStatus::from((string)$row['status']),
			startedAt: new DateTimeImmutable((string)$row['startedAt']),
			rating: $rating,
			finishedAt: $finishedAt,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function toRow(ReadingEntry $entry): array
	{
		return [
			'id' => $entry->getId(),
			'userId' => $entry->getUserId(),
			'bookId' => $entry->getBookId(),
			'status' => $entry->getStatus()->value,
			'startedAt' => $entry->getStartedAt()->format(DATE_ATOM),
			'rating' => $entry->getRating()?->getValue(),
			'finishedAt' => $entry->getFinishedAt()?->format(DATE_ATOM),
		];
	}

	/**
	 * @throws DateMalformedStringException
	 * @throws JsonException
	 */
	public function getById(string $id): ReadingEntry
	{
		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['id'] === $id)
			{
				return $this->hydrate($row);
			}
		}

		throw new ReadingEntryNotFoundException(sprintf('ReadingEntry "%s" not found.', $id));
	}

	/** @return array<ReadingEntry>
	 * @throws JsonException
	 * @throws DateMalformedStringException
	 */
	public function getByUserId(string $userId): array
	{
		$result = [];

		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['userId'] === $userId)
			{
				$result[] = $this->hydrate($row);
			}
		}

		return $result;
	}

	/** @return array<ReadingEntry>
	 * @throws JsonException
	 * @throws DateMalformedStringException
	 */
	public function getByBookId(string $bookId): array
	{
		$result = [];

		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['bookId'] === $bookId)
			{
				$result[] = $this->hydrate($row);
			}
		}

		return $result;
	}

	/**
	 * @throws JsonException
	 */
	public function save(ReadingEntry $entry): void
	{
		$data = $this->storage->load();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $entry->getId())
			{
				$data[$i] = $this->toRow($entry);
				$this->storage->write($data);

				return;
			}
		}

		$data[] = $this->toRow($entry);
		$this->storage->write($data);
	}

	/**
	 * @throws JsonException
	 */
	public function delete(string $id): void
	{
		$data = $this->storage->load();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $id)
			{
				unset($data[$i]);
				$this->storage->write($data);

				return;
			}
		}

		throw new ReadingEntryNotFoundException(sprintf('ReadingEntry "%s" not found.', $id));
	}

	/**
	 * @throws JsonException
	 */
	public function existsByUserAndBook(string $userId, string $bookId): bool
	{
		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['userId'] === $userId && (string)$row['bookId'] === $bookId)
			{
				return true;
			}
		}

		return false;
	}

}
