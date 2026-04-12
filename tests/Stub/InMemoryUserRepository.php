<?php

declare(strict_types=1);

namespace BookTracker\Tests\Stub;

use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\UserNotFoundException;
use BookTracker\Domain\Repository\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
	/** @var array<string, User> */
	private array $users = [];

	private int $nextId = 1;

	public function getById(string $id): User
	{
		if (!isset($this->users[$id]))
		{
			throw new UserNotFoundException(sprintf('User "%s" not found.', $id));
		}

		return $this->users[$id];
	}

	/** @return array<User> */
	public function getAll(): array
	{
		return array_values($this->users);
	}

	public function save(User $user): void
	{
		$this->users[$user->getId()] = $user;
	}

	public function delete(string $id): void
	{
		if (!isset($this->users[$id]))
		{
			throw new UserNotFoundException(sprintf('User "%s" not found.', $id));
		}

		unset($this->users[$id]);
	}

	public function existsByEmail(string $email): bool
	{
		foreach ($this->users as $user)
		{
			if ($user->getEmail() === $email)
			{
				return true;
			}
		}

		return false;
	}

	public function nextId(): string
	{
		return (string)$this->nextId++;
	}
}
