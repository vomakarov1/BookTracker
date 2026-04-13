<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Book;

use BookTracker\Application\Command\Book\DeleteBookCommand;
use BookTracker\Application\Command\Book\DeleteBookHandler;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use PHPUnit\Framework\TestCase;
use BookTracker\Domain\ValueObject\BookComplexity;

final class DeleteBookHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;
	private DeleteBookHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
		$this->handler = new DeleteBookHandler($this->repository);
	}

	public function testDeletesExistingBook(): void
	{
		$book = new Book('b1', 'Clean Code', 'Robert Martin', 'Tech', new BookComplexity(7));
		$this->repository->save($book);

		$this->handler->handle(new DeleteBookCommand('b1'));

		self::assertEmpty($this->repository->getAll());
	}

	public function testThrowsBookNotFoundExceptionWhenBookDoesNotExist(): void
	{
		$this->expectException(BookNotFoundException::class);
		$this->handler->handle(new DeleteBookCommand('non-existent'));
	}
}
