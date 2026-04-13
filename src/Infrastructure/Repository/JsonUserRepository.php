<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Repository;

use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\UserNotFoundException;
use BookTracker\Domain\Repository\UserRepositoryInterface;
use BookTracker\Infrastructure\Storage\JsonFileStorage;
use JsonException;

final class JsonUserRepository implements UserRepositoryInterface
{
	public function __construct(private readonly JsonFileStorage $storage)
	{
	}

	/**
	 * @param array<string, mixed> $row
	 */
	private function hydrate(array $row): User
	{
		return new User(
			id: (string)$row['id'],
			name: (string)$row['name'],
			email: (string)$row['email'],
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function toRow(User $user): array
	{
		return [
			'id' => $user->getId(),
			'name' => $user->getName(),
			'email' => $user->getEmail(),
		];
	}

	/**
	 * @throws JsonException
	 */
	public function getById(string $id): User
	{
		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['id'] === $id)
			{
				return $this->hydrate($row);
			}
		}

		throw new UserNotFoundException(sprintf('User "%s" not found.', $id));
	}

	/** @return array<User>
	 * @throws JsonException
	 */
	public function getAll(): array
	{
		$result = [];

		foreach ($this->storage->load() as $row)
		{
			$result[] = $this->hydrate($row);
		}

		return $result;
	}

	/**
	 * @throws JsonException
	 */
	public function save(User $user): void
	{
		$data = $this->storage->load();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $user->getId())
			{
				$data[$i] = $this->toRow($user);
				$this->storage->write($data);

				return;
			}
		}

		$data[] = $this->toRow($user);
		$this->storage->write($data);
	}

	/**
	 * @throws JsonException
	 */
	public function delete(string $id): void
	{
		$data = $this->storage->load();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $id)
			{
				unset($data[$i]);
				$this->storage->write($data);

				return;
			}
		}

		throw new UserNotFoundException(sprintf('User "%s" not found.', $id));
	}

	/**
	 * @throws JsonException
	 */
	public function existsByEmail(string $email): bool
	{
		foreach ($this->storage->load() as $row)
		{
			if ((string)$row['email'] === $email)
			{
				return true;
			}
		}

		return false;
	}
}
