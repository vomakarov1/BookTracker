<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Entity;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Exception\InvalidRatingException;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use PHPUnit\Framework\TestCase;
use BookTracker\Domain\ValueObject\BookComplexity;

final class ReadingEntryTest extends TestCase
{
	private User $user;
	private Book $book;

	protected function setUp(): void
	{
		$this->user = new User('u1', 'John Doe', 'john@example.com');
		$this->book = new Book('b1', 'Clean Code', 'Robert C. Martin', 'Programming', new BookComplexity(7));
	}

	public function testCreateReturnsEntryWithCorrectData(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book);

		$this->assertSame('u1', $entry->getUserId());
		$this->assertSame('b1', $entry->getBookId());
		$this->assertSame(ReadingStatus::PLANNED, $entry->getStatus());
		$this->assertNull($entry->getRating());
		$this->assertNull($entry->getFinishedAt());
	}

	public function testChangeStatusPlannedToReadingSucceeds(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book);
		$entry->changeStatus(ReadingStatus::READING);

		$this->assertSame(ReadingStatus::READING, $entry->getStatus());
	}

	public function testChangeStatusPlannedToFinishedThrows(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book);

		$this->expectException(InvalidStatusTransitionException::class);
		$entry->changeStatus(ReadingStatus::FINISHED);
	}

	public function testChangeStatusReadingToFinishedSetsFinishedAt(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book);
		$entry->changeStatus(ReadingStatus::READING);
		$entry->changeStatus(ReadingStatus::FINISHED);

		$this->assertSame(ReadingStatus::FINISHED, $entry->getStatus());
		$this->assertNotNull($entry->getFinishedAt());
	}

	public function testRateFinishedEntrySucceeds(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book);
		$entry->changeStatus(ReadingStatus::READING);
		$entry->changeStatus(ReadingStatus::FINISHED);

		$rating = new ReadingEntryRating(8);
		$entry->rate($rating);

		$this->assertSame(8, $entry->getRating()?->getValue());
	}

	public function testRateReadingEntryThrows(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book);
		$entry->changeStatus(ReadingStatus::READING);

		$this->expectException(InvalidRatingException::class);
		$entry->rate(new ReadingEntryRating(5));
	}
}
