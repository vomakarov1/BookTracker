<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Repository;

use BookTracker\Domain\Entity\User;
use BookTracker\Domain\Exception\UserNotFoundException;
use BookTracker\Domain\Repository\UserRepositoryInterface;
use JsonException;
use RuntimeException;

final class JsonUserRepository implements UserRepositoryInterface
{
	private string $filePath;

	public function __construct(string $storagePath)
	{
		$this->filePath = rtrim($storagePath, '/') . '/users.json';

		if (!is_dir($storagePath) && !mkdir($storagePath, 0755, true) && !is_dir($storagePath))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $storagePath));
		}

		if (!file_exists($this->filePath))
		{
			file_put_contents($this->filePath, '[]');
		}
	}

	/**
	 * @return array<int, array<string, mixed>>
	 * @throws JsonException
	 */
	private function loadData(): array
	{
		$content = file_get_contents($this->filePath);

		if ($content === false)
		{
			throw new RuntimeException(sprintf('Failed to read storage file: %s', $this->filePath));
		}

		$decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($decoded))
		{
			throw new RuntimeException(sprintf('Storage file contains invalid data: %s', $this->filePath));
		}

		/** @var array<int, array<string, mixed>> $decoded */
		return $decoded;
	}

	/**
	 * @param array<int, array<string, mixed>> $data
	 * @throws JsonException
	 */
	private function writeData(array $data): void
	{
		file_put_contents(
			$this->filePath,
			json_encode(array_values($data), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		);
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
		foreach ($this->loadData() as $row)
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

		foreach ($this->loadData() as $row)
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
		$data = $this->loadData();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $user->getId())
			{
				$data[$i] = $this->toRow($user);
				$this->writeData($data);

				return;
			}
		}

		$data[] = $this->toRow($user);
		$this->writeData($data);
	}

	/**
	 * @throws JsonException
	 */
	public function delete(string $id): void
	{
		$data = $this->loadData();

		foreach ($data as $i => $row)
		{
			if ((string)$row['id'] === $id)
			{
				unset($data[$i]);
				$this->writeData($data);

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
		foreach ($this->loadData() as $row)
		{
			if ((string)$row['email'] === $email)
			{
				return true;
			}
		}

		return false;
	}
}
