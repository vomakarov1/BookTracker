<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Reading;

use BookTracker\Application\Command\ReadingEntry\ChangeReadingStatusCommand;
use BookTracker\Application\Command\ReadingEntry\ChangeReadingStatusHandler;
use BookTracker\Application\Exception\ValidationException;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Exception\InvalidStatusTransitionException;
use BookTracker\Domain\Exception\ReadingEntryNotFoundException;
use BookTracker\Tests\Stub\InMemoryReadingEntryRepository;
use PHPUnit\Framework\TestCase;
use BookTracker\Domain\ValueObject\BookComplexity;

final class ChangeReadingStatusHandlerTest extends TestCase
{
	private InMemoryReadingEntryRepository $repository;
	private ChangeReadingStatusHandler $handler;
	private User $user;
	private Book $book;

	protected function setUp(): void
	{
		$this->repository = new InMemoryReadingEntryRepository();
		$this->handler = new ChangeReadingStatusHandler($this->repository);
		$this->user = new User('u1', 'Alice', 'alice@example.com');
		$this->book = new Book('b1', 'Clean Code', 'Robert Martin', 'Tech', new BookComplexity(7));
	}

	public function testTransitionsFromPlannedToReading(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book, ReadingStatus::PLANNED);
		$this->repository->save($entry);

		$this->handler->handle(new ChangeReadingStatusCommand('e1', 'reading'));

		self::assertSame(ReadingStatus::READING, $this->repository->getById('e1')->getStatus());
	}

	public function testThrowsInvalidStatusTransitionExceptionForPlannedToFinished(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book, ReadingStatus::PLANNED);
		$this->repository->save($entry);

		$this->expectException(InvalidStatusTransitionException::class);
		$this->handler->handle(new ChangeReadingStatusCommand('e1', 'finished'));
	}

	public function testThrowsValidationExceptionForInvalidStatusString(): void
	{
		$entry = ReadingEntry::create('e1', $this->user, $this->book, ReadingStatus::PLANNED);
		$this->repository->save($entry);

		$this->expectException(ValidationException::class);
		$this->handler->handle(new ChangeReadingStatusCommand('e1', 'invalid_status'));
	}

	public function testThrowsReadingEntryNotFoundExceptionForNonExistentEntry(): void
	{
		$this->expectException(ReadingEntryNotFoundException::class);
		$this->handler->handle(new ChangeReadingStatusCommand('non-existent', 'reading'));
	}
}
