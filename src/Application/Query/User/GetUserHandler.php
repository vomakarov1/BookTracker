<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\User;

use BookTracker\Application\DTO\UserDTO;
use BookTracker\Domain\Repository\UserRepositoryInterface;

final class GetUserHandler
{
	public function __construct(
		private readonly UserRepositoryInterface $userRepository,
	)
	{
	}

	public function handle(GetUserQuery $query): UserDTO
	{
		$user = $this->userRepository->getById($query->id);

		return new UserDTO(
			id: $user->getId(),
			name: $user->getName(),
			email: $user->getEmail(),
		);
	}
}
