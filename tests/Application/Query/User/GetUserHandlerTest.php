<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Query\User;

use BookTracker\Application\Query\User\GetUserHandler;
use BookTracker\Application\Query\User\GetUserQuery;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\UserNotFoundException;
use BookTracker\Tests\Stub\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class GetUserHandlerTest extends TestCase
{
	private InMemoryUserRepository $repository;
	private GetUserHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryUserRepository();
		$this->handler = new GetUserHandler($this->repository);
	}

	public function testReturnsUserDTOForExistingUser(): void
	{
		$user = new User('1', 'Alice', 'alice@example.com');
		$this->repository->save($user);

		$dto = $this->handler->handle(new GetUserQuery('1'));

		self::assertSame('1', $dto->id);
		self::assertSame('Alice', $dto->name);
		self::assertSame('alice@example.com', $dto->email);
	}

	public function testThrowsUserNotFoundExceptionForMissingUser(): void
	{
		$this->expectException(UserNotFoundException::class);

		$this->handler->handle(new GetUserQuery('non-existent'));
	}
}
