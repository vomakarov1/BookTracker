<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Reading;

use BookTracker\Application\Command\ReadingEntry\RateReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\RateReadingEntryHandler;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Exception\InvalidRatingException;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use BookTracker\Tests\Stub\InMemoryReadingEntryRepository;
use PHPUnit\Framework\TestCase;

final class RateReadingEntryHandlerTest extends TestCase
{
	private InMemoryReadingEntryRepository $repository;
	private RateReadingEntryHandler $handler;
	private User $user;
	private Book $book;

	protected function setUp(): void
	{
		$this->repository = new InMemoryReadingEntryRepository();
		$this->handler = new RateReadingEntryHandler($this->repository);
		$this->user = new User('u1', 'Alice', 'alice@example.com');
		$this->book = new Book('b1', 'Clean Code', 'Robert Martin', 'Tech', 7);
	}

	public function testRatesFinishedEntryAndSavesRating(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book, ReadingStatus::FINISHED);
		$this->repository->save($entry);

		$this->handler->handle(new RateReadingEntryCommand('e1', 8));

		$saved = $this->repository->getById('e1');
		self::assertNotNull($saved->getRating());
		self::assertSame(8, $saved->getRating()->getValue());
	}

	public function testThrowsInvalidStatusTransitionExceptionWhenRatingReadingEntry(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book, ReadingStatus::READING);
		$this->repository->save($entry);

		$this->expectException(InvalidStatusTransitionException::class);
		$this->handler->handle(new RateReadingEntryCommand('e1', 8));
	}

	public function testThrowsInvalidRatingExceptionForOutOfRangeRating(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book, ReadingStatus::FINISHED);
		$this->repository->save($entry);

		$this->expectException(InvalidRatingException::class);
		$this->handler->handle(new RateReadingEntryCommand('e1', 11));
	}

	public function testThrowsReadingEntryNotFoundExceptionForNonExistentEntry(): void
	{
		$this->expectException(ReadingEntryNotFoundException::class);
		$this->handler->handle(new RateReadingEntryCommand('non-existent', 8));
	}
}
