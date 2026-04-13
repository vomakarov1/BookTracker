<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Query\ReadingEntry;

use BookTracker\Application\Query\ReadingEntry\GetReadingEntriesHandler;
use BookTracker\Application\Query\ReadingEntry\GetReadingEntriesQuery;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Tests\Stub\InMemoryReadingEntryRepository;
use PHPUnit\Framework\TestCase;
use BookTracker\Domain\ValueObject\BookComplexity;

final class GetReadingEntriesHandlerTest extends TestCase
{
	private InMemoryReadingEntryRepository $repository;
	private GetReadingEntriesHandler $handler;
	private User $user;

	protected function setUp(): void
	{
		$this->repository = new InMemoryReadingEntryRepository();
		$this->handler = new GetReadingEntriesHandler($this->repository);
		$this->user = new User('u1', 'Alice', 'alice@example.com');
	}

	public function testReturnsTwoEntriesForUserWithTwoReadings(): void
	{
		$book1 = new Book('b1', 'Book One', 'Author A', 'Fiction', new BookComplexity(3));
		$book2 = new Book('b2', 'Book Two', 'Author B', 'Tech', new BookComplexity(7));

		$entry1 = ReadingEntry::create('e1', $this->user, $book1);
		$entry2 = ReadingEntry::create('e2', $this->user, $book2);

		$this->repository->save($entry1);
		$this->repository->save($entry2);

		$result = $this->handler->handle(new GetReadingEntriesQuery('u1'));

		self::assertCount(2, $result);
		self::assertSame('u1', $result[0]->userId);
		self::assertSame('u1', $result[1]->userId);
	}

	public function testReturnsEmptyArrayForUserWithNoEntries(): void
	{
		$result = $this->handler->handle(new GetReadingEntriesQuery('u1'));

		self::assertSame([], $result);
	}

	public function testDoesNotReturnEntriesOfOtherUsers(): void
	{
		$otherUser = new User('u2', 'Bob', 'bob@example.com');
		$book = new Book('b1', 'Book One', 'Author A', 'Fiction', new BookComplexity(3));

		$entry = ReadingEntry::create('e1', $otherUser, $book);
		$this->repository->save($entry);

		$result = $this->handler->handle(new GetReadingEntriesQuery('u1'));

		self::assertSame([], $result);
	}
}
