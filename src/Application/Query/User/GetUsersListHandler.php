<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\User;

use BookTracker\Application\DTO\UserDTO;
use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Repository\UserRepositoryInterface;

final class GetUsersListHandler
{
	public function __construct(
		private readonly UserRepositoryInterface $userRepository,
	)
	{
	}

	/** @return array<UserDTO> */
	public function handle(GetUsersListQuery $query): array
	{
		return array_map(
			static fn(User $u) => new UserDTO(
				id: $u->getId(),
				name: $u->getName(),
				email: $u->getEmail(),
			),
			$this->userRepository->getAll(),
		);
	}
}
