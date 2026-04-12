<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\User;

use BookTracker\Domain\Repository\UserRepositoryInterface;

final class DeleteUserHandler
{
	public function __construct(
		private readonly UserRepositoryInterface $userRepository,
	)
	{
	}

	public function handle(DeleteUserCommand $command): void
	{
		$this->userRepository->getById($command->id);
		$this->userRepository->delete($command->id);
	}
}
