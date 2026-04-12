<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Query\Book;

use BookTracker\Application\Query\Book\GetBookHandler;
use BookTracker\Application\Query\Book\GetBookQuery;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\BookNotFoundException;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use PHPUnit\Framework\TestCase;

final class GetBookHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;
	private GetBookHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
		$this->handler = new GetBookHandler($this->repository);
	}

	public function testReturnsBookDTOForExistingBook(): void
	{
		$book = new Book('1', 'Clean Code', 'Robert Martin', 'Tech', 7);
		$this->repository->save($book);

		$dto = $this->handler->handle(new GetBookQuery('1'));

		self::assertSame('1', $dto->id);
		self::assertSame('Clean Code', $dto->title);
		self::assertSame('Robert Martin', $dto->author);
		self::assertSame('Tech', $dto->category);
		self::assertSame(7, $dto->complexity);
	}

	public function testThrowsBookNotFoundExceptionForMissingBook(): void
	{
		$this->expectException(BookNotFoundException::class);

		$this->handler->handle(new GetBookQuery('non-existent'));
	}
}
