<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\User;

use BookTracker\Application\Command\User\DeleteUserCommand;
use BookTracker\Application\Command\User\DeleteUserHandler;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\UserNotFoundException;
use BookTracker\Tests\Stub\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class DeleteUserHandlerTest extends TestCase
{
	private InMemoryUserRepository $repository;
	private DeleteUserHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryUserRepository();
		$this->handler = new DeleteUserHandler($this->repository);
	}

	public function testDeletesExistingUser(): void
	{
		$user = new User('u1', 'Alice', 'alice@example.com');
		$this->repository->save($user);

		$this->handler->handle(new DeleteUserCommand('u1'));

		self::assertEmpty($this->repository->getAll());
	}

	public function testThrowsUserNotFoundExceptionWhenUserDoesNotExist(): void
	{
		$this->expectException(UserNotFoundException::class);
		$this->handler->handle(new DeleteUserCommand('non-existent'));
	}
}
