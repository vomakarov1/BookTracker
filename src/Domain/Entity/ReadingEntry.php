<?php

declare(strict_types=1);

namespace BookTracker\Domain\Entity;

use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use DateTimeImmutable;

final class ReadingEntry
{
	private ?ReadingEntryRating $rating = null;
	private ?DateTimeImmutable $finishedAt = null;

	private function __construct(
		private readonly string $id,
		private readonly string $userId,
		private readonly string $bookId,
		private ReadingStatus $status,
		private readonly DateTimeImmutable $startedAt,
	)
	{
	}

	public static function create(
		string $id,
		User $user,
		Book $book,
		ReadingStatus $status = ReadingStatus::PLANNED,
	): self
	{
		return new self(
			id: $id,
			userId: $user->getId(),
			bookId: $book->getId(),
			status: $status,
			startedAt: new DateTimeImmutable(),
		);
	}

	public static function reconstruct(
		string $id,
		string $userId,
		string $bookId,
		ReadingStatus $status,
		DateTimeImmutable $startedAt,
		?ReadingEntryRating $rating = null,
		?DateTimeImmutable $finishedAt = null,
	): self
	{
		$entry = new self(
			id: $id,
			userId: $userId,
			bookId: $bookId,
			status: $status,
			startedAt: $startedAt,
		);
		$entry->rating = $rating;
		$entry->finishedAt = $finishedAt;

		return $entry;
	}

	public function changeStatus(ReadingStatus $new): void
	{
		if (!$this->status->canTransitionTo($new))
		{
			throw new InvalidStatusTransitionException(
				sprintf(
					'Cannot transition from %s to %s.',
					$this->status->value,
					$new->value,
				)
			);
		}

		$this->status = $new;

		if ($new === ReadingStatus::FINISHED)
		{
			$this->finishedAt = new DateTimeImmutable();
		}
	}

	public function rate(ReadingEntryRating $rating): void
	{
		if ($this->status !== ReadingStatus::FINISHED)
		{
			throw new InvalidStatusTransitionException(
				sprintf(
					'Cannot rate a reading entry with status %s. Only FINISHED entries can be rated.',
					$this->status->value,
				)
			);
		}

		$this->rating = $rating;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getUserId(): string
	{
		return $this->userId;
	}

	public function getBookId(): string
	{
		return $this->bookId;
	}

	public function getStatus(): ReadingStatus
	{
		return $this->status;
	}

	public function getRating(): ?ReadingEntryRating
	{
		return $this->rating;
	}

	public function getStartedAt(): DateTimeImmutable
	{
		return $this->startedAt;
	}

	public function getFinishedAt(): ?DateTimeImmutable
	{
		return $this->finishedAt;
	}
}
