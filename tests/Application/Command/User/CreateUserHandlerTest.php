<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\User;

use BookTracker\Application\Command\User\CreateUserCommand;
use BookTracker\Application\Command\User\CreateUserHandler;
use BookTracker\Domain\Exception\DuplicateUserException;
use BookTracker\Tests\Stub\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
	private InMemoryUserRepository $repository;
	private CreateUserHandler $handler;

	protected function setUp(): void
	{
		$this->repository = new InMemoryUserRepository();
		$this->handler = new CreateUserHandler($this->repository);
	}

	public function testCreatesUserWithCorrectFields(): void
	{
		$command = new CreateUserCommand('Alice', 'alice@example.com');

		$id = $this->handler->handle($command);

		$user = $this->repository->getById($id);
		self::assertSame('Alice', $user->getName());
		self::assertSame('alice@example.com', $user->getEmail());
	}

	public function testThrowsDuplicateUserExceptionOnDuplicateEmail(): void
	{
		$this->handler->handle(new CreateUserCommand('Alice', 'alice@example.com'));

		$this->expectException(DuplicateUserException::class);
		$this->handler->handle(new CreateUserCommand('Alice2', 'alice@example.com'));
	}
}
