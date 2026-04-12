<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\User;

use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\DuplicateUserException;
use BookTracker\Domain\Repository\UserRepositoryInterface;

final class CreateUserHandler
{
	public function __construct(
		private readonly UserRepositoryInterface $userRepository,
	)
	{
	}

	public function handle(CreateUserCommand $command): string
	{
		if ($this->userRepository->existsByEmail($command->email))
		{
			throw new DuplicateUserException(
				sprintf('User with email "%s" already exists.', $command->email)
			);
		}

		$id = $this->userRepository->nextId();

		$user = new User(
			id: $id,
			name: $command->name,
			email: $command->email,
		);

		$this->userRepository->save($user);

		return $id;
	}
}
