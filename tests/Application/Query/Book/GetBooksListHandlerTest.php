<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Query\Book;

use BookTracker\Application\Query\Book\GetBooksListHandler;
use BookTracker\Application\Query\Book\GetBooksListQuery;
use BookTracker\Domain\Entity\Book;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use PHPUnit\Framework\TestCase;

final class GetBooksListHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;
	private GetBooksListHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
		$this->handler = new GetBooksListHandler($this->repository);
	}

	public function testReturnsEmptyArrayForEmptyRepository(): void
	{
		$result = $this->handler->handle(new GetBooksListQuery());

		self::assertSame([], $result);
	}

	public function testReturnsAllBooksWhenNoFilters(): void
	{
		$this->repository->save(new Book('1', 'Book One', 'Author A', 'Fiction', 3));
		$this->repository->save(new Book('2', 'Book Two', 'Author B', 'Tech', 7));
		$this->repository->save(new Book('3', 'Book Three', 'Author C', 'History', 5));

		$result = $this->handler->handle(new GetBooksListQuery());

		self::assertCount(3, $result);
	}

	public function testFiltersByCategory(): void
	{
		$this->repository->save(new Book('1', 'Book One', 'Author A', 'Fiction', 3));
		$this->repository->save(new Book('2', 'Book Two', 'Author B', 'Tech', 7));
		$this->repository->save(new Book('3', 'Book Three', 'Author C', 'Fiction', 5));

		$result = $this->handler->handle(new GetBooksListQuery(category: 'Fiction'));

		self::assertCount(2, $result);
		foreach ($result as $dto)
		{
			self::assertSame('Fiction', $dto->category);
		}
	}

	public function testFiltersByAuthor(): void
	{
		$this->repository->save(new Book('1', 'Book One', 'Robert Martin', 'Tech', 7));
		$this->repository->save(new Book('2', 'Book Two', 'Martin Fowler', 'Tech', 6));
		$this->repository->save(new Book('3', 'Book Three', 'Robert Martin', 'Tech', 8));

		$result = $this->handler->handle(new GetBooksListQuery(author: 'Robert Martin'));

		self::assertCount(2, $result);
		foreach ($result as $dto)
		{
			self::assertSame('Robert Martin', $dto->author);
		}
	}
}
