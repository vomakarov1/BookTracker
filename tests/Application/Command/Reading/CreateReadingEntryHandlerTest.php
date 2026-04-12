<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Reading;

use BookTracker\Application\Command\ReadingEntry\CreateReadingEntryCommand;
use BookTracker\Application\Command\ReadingEntry\CreateReadingEntryHandler;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Domain\Exception\DuplicateReadingEntryException;
use BookTracker\Domain\Exception\UserNotFoundException;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use BookTracker\Tests\Stub\InMemoryIdGenerator;
use BookTracker\Tests\Stub\InMemoryReadingEntryRepository;
use BookTracker\Tests\Stub\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class CreateReadingEntryHandlerTest extends TestCase
{
	private InMemoryUserRepository $userRepository;
	private InMemoryBookRepository $bookRepository;
	private InMemoryReadingEntryRepository $entryRepository;
	private CreateReadingEntryHandler $handler;

	protected function setUp(): void
	{
		$this->userRepository = new InMemoryUserRepository();
		$this->bookRepository = new InMemoryBookRepository();
		$this->entryRepository = new InMemoryReadingEntryRepository();
		$this->handler = new CreateReadingEntryHandler(
			$this->userRepository,
			$this->bookRepository,
			$this->entryRepository,
			new InMemoryIdGenerator(),
		);

		$this->userRepository->save(new User('u1', 'Alice', 'alice@example.com'));
		$this->bookRepository->save(new Book('b1', 'Clean Code', 'Robert Martin', 'Tech', 7));
	}

	public function testCreatesReadingEntryWithCorrectUserAndBook(): void
	{
		$id = $this->handler->handle(new CreateReadingEntryCommand('u1', 'b1'));

		$entry = $this->entryRepository->getById($id);
		self::assertSame('u1', $entry->getUserId());
		self::assertSame('b1', $entry->getBookId());
	}

	public function testThrowsUserNotFoundExceptionForNonExistentUser(): void
	{
		$this->expectException(UserNotFoundException::class);
		$this->handler->handle(new CreateReadingEntryCommand('non-existent', 'b1'));
	}

	public function testThrowsBookNotFoundExceptionForNonExistentBook(): void
	{
		$this->expectException(BookNotFoundException::class);
		$this->handler->handle(new CreateReadingEntryCommand('u1', 'non-existent'));
	}

	public function testThrowsDuplicateReadingEntryExceptionForDuplicateUserAndBook(): void
	{
		$this->handler->handle(new CreateReadingEntryCommand('u1', 'b1'));

		$this->expectException(DuplicateReadingEntryException::class);
		$this->handler->handle(new CreateReadingEntryCommand('u1', 'b1'));
	}
}
