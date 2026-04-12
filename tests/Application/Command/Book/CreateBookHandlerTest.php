<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Book;

use BookTracker\Application\Command\Book\CreateBookCommand;
use BookTracker\Application\Command\Book\CreateBookHandler;
use BookTracker\Domain\Exception\DuplicateBookException;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use PHPUnit\Framework\TestCase;

final class CreateBookHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;
	private CreateBookHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
		$this->handler = new CreateBookHandler($this->repository);
	}

	public function testCreatesBookWithCorrectFields(): void
	{
		$command = new CreateBookCommand('Clean Code', 'Robert Martin', 'Tech', 7);

		$id = $this->handler->handle($command);

		$book = $this->repository->getById($id);
		self::assertSame('Clean Code', $book->getTitle());
		self::assertSame('Robert Martin', $book->getAuthor());
		self::assertSame('Tech', $book->getCategory());
		self::assertSame(7, $book->getComplexity());
	}

	public function testThrowsDuplicateBookExceptionOnDuplicateTitle(): void
	{
		$this->handler->handle(new CreateBookCommand('Clean Code', 'Robert Martin', 'Tech', 7));

		$this->expectException(DuplicateBookException::class);
		$this->handler->handle(new CreateBookCommand('Clean Code', 'Another Author', 'Fiction', 3));
	}
}
