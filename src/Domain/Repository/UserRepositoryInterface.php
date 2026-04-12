<?php

declare(strict_types=1);

namespace BookTracker\Domain\Repository;

use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\UserNotFoundException;

interface UserRepositoryInterface
{
	/**
	 * @throws UserNotFoundException
	 */
	public function getById(string $id): User;

	/**
	 * @return array<User>
	 */
	public function getAll(): array;

	public function save(User $user): void;

	/**
	 * @throws UserNotFoundException
	 */
	public function delete(string $id): void;

	public function existsByEmail(string $email): bool;

	public function nextId(): string;
}
