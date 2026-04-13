<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Repository;

use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use DateMalformedStringException;
use DateTimeImmutable;
use JsonException;
use RuntimeException;

final class JsonReadingEntryRepository implements ReadingEntryRepositoryInterface
{
	private string $filePath;

	public function __construct(string $storagePath)
	{
		$this->filePath = rtrim($storagePath, '/') . '/reading_entries.json';

		if (!is_dir($storagePath) && !mkdir($storagePath, 0755, true) && !is_dir($storagePath))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $storagePath));
		}

		if (!file_exists($this->filePath))
		{
			file_put_contents($this->filePath, '[]');
		}
	}

	/**
	 * @return array<int, array<string, mixed>>
	 * @throws JsonException
	 */
	private function loadData(): array
	{
		$content = file_get_contents($this->filePath);

		if ($content === false)
		{
			throw new RuntimeException(sprintf('Failed to read storage file: %s', $this->filePath));
		}

		$decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($decoded))
		{
			throw new RuntimeException(sprintf('Storage file contains invalid data: %s', $this->filePath));
		}

		/** @var array<int, array<string, mixed>> $decoded */
		return $decoded;
	}

	/**
	 * @param array<int, array<string, mixed>> $data
	 * @throws JsonException
	 */
	private function writeData(array $data): void
	{
		file_put_contents(
			$this->filePath,
			json_encode(array_values($data), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		);
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
		foreach ($this->loadData() as $row)
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

		foreach ($this->loadData() as $row)
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

		foreach ($this->loadData() as $row)
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
		$data = $this->loadData();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $entry->getId())
			{
				$data[$i] = $this->toRow($entry);
				$this->writeData($data);

				return;
			}
		}

		$data[] = $this->toRow($entry);
		$this->writeData($data);
	}

	/**
	 * @throws JsonException
	 */
	public function delete(string $id): void
	{
		$data = $this->loadData();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $id)
			{
				unset($data[$i]);
				$this->writeData($data);

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
		foreach ($this->loadData() as $row)
		{
			if ((string)$row['userId'] === $userId && (string)$row['bookId'] === $bookId)
			{
				return true;
			}
		}

		return false;
	}

}
