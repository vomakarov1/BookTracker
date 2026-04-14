<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Query\Statistics;

use BookTracker\Application\Query\Statistics\GetReadingStatsHandler;
use BookTracker\Application\Query\Statistics\GetReadingStatsQuery;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\ValueObject\BookComplexity;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use BookTracker\Tests\Stub\InMemoryReadingEntryRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class GetReadingStatsHandlerTest extends TestCase
{
	private InMemoryBookRepository $bookRepository;
	private InMemoryReadingEntryRepository $entryRepository;
	private GetReadingStatsHandler $handler;
	private User $user;

	protected function setUp(): void
	{
		$this->bookRepository = new InMemoryBookRepository();
		$this->entryRepository = new InMemoryReadingEntryRepository();
		$this->handler = new GetReadingStatsHandler($this->entryRepository, $this->bookRepository);
		$this->user = new User('u1', 'Alice', 'alice@example.com');
	}

	public function testReturnsZeroCountsForUserWithNoEntries(): void
	{
		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		self::assertSame(0, $stats->countsByStatus[ReadingStatus::PLANNED->value]);
		self::assertSame(0, $stats->countsByStatus[ReadingStatus::READING->value]);
		self::assertSame(0, $stats->countsByStatus[ReadingStatus::FINISHED->value]);
		self::assertSame(0, $stats->countsByStatus[ReadingStatus::DROPPED->value]);
		self::assertSame([], $stats->averageRatingByAuthor);
		self::assertSame([], $stats->finishedByMonth);
	}

	public function testCountsByStatusAreCorrect(): void
	{
		$book1 = new Book('b1', 'Book One', 'Author A', 'Fiction', new BookComplexity(3));
		$book2 = new Book('b2', 'Book Two', 'Author A', 'Fiction', new BookComplexity(5));
		$book3 = new Book('b3', 'Book Three', 'Author B', 'Tech', new BookComplexity(7));
		$book4 = new Book('b4', 'Book Four', 'Author B', 'Tech', new BookComplexity(4));

		$this->bookRepository->save($book1);
		$this->bookRepository->save($book2);
		$this->bookRepository->save($book3);
		$this->bookRepository->save($book4);

		$planned1 = ReadingEntry::create('e1', $this->user, $book1);
		$planned2 = ReadingEntry::create('e2', $this->user, $book2);

		$reading = ReadingEntry::create('e3', $this->user, $book3);
		$reading->changeStatus(ReadingStatus::READING);

		$dropped = ReadingEntry::create('e4', $this->user, $book4);
		$dropped->changeStatus(ReadingStatus::DROPPED);

		$this->entryRepository->save($planned1);
		$this->entryRepository->save($planned2);
		$this->entryRepository->save($reading);
		$this->entryRepository->save($dropped);

		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		self::assertSame(2, $stats->countsByStatus[ReadingStatus::PLANNED->value]);
		self::assertSame(1, $stats->countsByStatus[ReadingStatus::READING->value]);
		self::assertSame(0, $stats->countsByStatus[ReadingStatus::FINISHED->value]);
		self::assertSame(1, $stats->countsByStatus[ReadingStatus::DROPPED->value]);
	}

	public function testAverageRatingByAuthorIsCorrect(): void
	{
		$bookA1 = new Book('b1', 'Book A1', 'Author A', 'Fiction', new BookComplexity(5));
		$bookA2 = new Book('b2', 'Book A2', 'Author A', 'Fiction', new BookComplexity(6));
		$bookB1 = new Book('b3', 'Book B1', 'Author B', 'Tech', new BookComplexity(7));

		$this->bookRepository->save($bookA1);
		$this->bookRepository->save($bookA2);
		$this->bookRepository->save($bookB1);

		// Author A: ratings 8 and 6 → avg 7.0
		$entryA1 = ReadingEntry::reconstruct(
			id: 'e1',
			userId: 'u1',
			bookId: 'b1',
			status: ReadingStatus::FINISHED,
			startedAt: new DateTimeImmutable('2025-01-01'),
			rating: new ReadingEntryRating(8),
			finishedAt: new DateTimeImmutable('2025-01-10'),
		);
		$entryA2 = ReadingEntry::reconstruct(
			id: 'e2',
			userId: 'u1',
			bookId: 'b2',
			status: ReadingStatus::FINISHED,
			startedAt: new DateTimeImmutable('2025-02-01'),
			rating: new ReadingEntryRating(6),
			finishedAt: new DateTimeImmutable('2025-02-10'),
		);

		// Author B: rating 9 → avg 9.0
		$entryB1 = ReadingEntry::reconstruct(
			id: 'e3',
			userId: 'u1',
			bookId: 'b3',
			status: ReadingStatus::FINISHED,
			startedAt: new DateTimeImmutable('2025-03-01'),
			rating: new ReadingEntryRating(9),
			finishedAt: new DateTimeImmutable('2025-03-10'),
		);

		$this->entryRepository->save($entryA1);
		$this->entryRepository->save($entryA2);
		$this->entryRepository->save($entryB1);

		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		self::assertArrayHasKey('Author A', $stats->averageRatingByAuthor);
		self::assertArrayHasKey('Author B', $stats->averageRatingByAuthor);
		self::assertSame(7.0, $stats->averageRatingByAuthor['Author A']);
		self::assertSame(9.0, $stats->averageRatingByAuthor['Author B']);
	}

	public function testAverageRatingByAuthorIsSortedDescending(): void
	{
		$bookA = new Book('b1', 'Book A', 'Author A', 'Fiction', new BookComplexity(5));
		$bookB = new Book('b2', 'Book B', 'Author B', 'Fiction', new BookComplexity(5));
		$bookC = new Book('b3', 'Book C', 'Author C', 'Fiction', new BookComplexity(5));

		$this->bookRepository->save($bookA);
		$this->bookRepository->save($bookB);
		$this->bookRepository->save($bookC);

		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e1',
				userId: 'u1',
				bookId: 'b1',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-01-01'),
				rating: new ReadingEntryRating(5),
				finishedAt: new DateTimeImmutable('2025-01-10'),
			),
		);
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e2',
				userId: 'u1',
				bookId: 'b2',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-01-01'),
				rating: new ReadingEntryRating(9),
				finishedAt: new DateTimeImmutable('2025-01-10'),
			),
		);
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e3',
				userId: 'u1',
				bookId: 'b3',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-01-01'),
				rating: new ReadingEntryRating(7),
				finishedAt: new DateTimeImmutable('2025-01-10'),
			),
		);

		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		$authors = array_keys($stats->averageRatingByAuthor);
		self::assertSame('Author B', $authors[0]);
		self::assertSame('Author C', $authors[1]);
		self::assertSame('Author A', $authors[2]);
	}

	public function testFinishedByMonthGroupsCorrectly(): void
	{
		$book1 = new Book('b1', 'Book One', 'Author A', 'Fiction', new BookComplexity(3));
		$book2 = new Book('b2', 'Book Two', 'Author A', 'Fiction', new BookComplexity(4));
		$book3 = new Book('b3', 'Book Three', 'Author B', 'Tech', new BookComplexity(5));

		$this->bookRepository->save($book1);
		$this->bookRepository->save($book2);
		$this->bookRepository->save($book3);

		// Two books finished in January 2025
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e1',
				userId: 'u1',
				bookId: 'b1',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-01-01'),
				finishedAt: new DateTimeImmutable('2025-01-15'),
			),
		);
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e2',
				userId: 'u1',
				bookId: 'b2',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-01-20'),
				finishedAt: new DateTimeImmutable('2025-01-28'),
			),
		);

		// One book finished in March 2025
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e3',
				userId: 'u1',
				bookId: 'b3',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-03-01'),
				finishedAt: new DateTimeImmutable('2025-03-10'),
			),
		);

		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		self::assertSame(2, $stats->finishedByMonth['2025-01']);
		self::assertSame(1, $stats->finishedByMonth['2025-03']);
		self::assertArrayNotHasKey('2025-02', $stats->finishedByMonth);
	}

	public function testFinishedByMonthIsSortedChronologically(): void
	{
		$book1 = new Book('b1', 'Book One', 'Author A', 'Fiction', new BookComplexity(3));
		$book2 = new Book('b2', 'Book Two', 'Author A', 'Fiction', new BookComplexity(4));

		$this->bookRepository->save($book1);
		$this->bookRepository->save($book2);

		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e1',
				userId: 'u1',
				bookId: 'b1',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-06-01'),
				finishedAt: new DateTimeImmutable('2025-06-10'),
			),
		);
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e2',
				userId: 'u1',
				bookId: 'b2',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-02-01'),
				finishedAt: new DateTimeImmutable('2025-02-10'),
			),
		);

		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		$months = array_keys($stats->finishedByMonth);
		self::assertSame('2025-02', $months[0]);
		self::assertSame('2025-06', $months[1]);
	}

	public function testUnratedEntriesAreNotCountedInAuthorRatings(): void
	{
		$book = new Book('b1', 'Book One', 'Author A', 'Fiction', new BookComplexity(5));
		$this->bookRepository->save($book);

		// Finished but not rated
		$this->entryRepository->save(
			ReadingEntry::reconstruct(
				id: 'e1',
				userId: 'u1',
				bookId: 'b1',
				status: ReadingStatus::FINISHED,
				startedAt: new DateTimeImmutable('2025-01-01'),
				finishedAt: new DateTimeImmutable('2025-01-10'),
			),
		);

		$stats = $this->handler->handle(new GetReadingStatsQuery('u1'));

		self::assertSame([], $stats->averageRatingByAuthor);
	}
}
