<?php

declare(strict_types=1);

namespace BookTracker\Domain\Entity;

use BookTracker\Domain\Exception\InvalidUserException;

final class User
{
	public function __construct(
		private readonly string $id,
		private readonly string $name,
		private readonly string $email,
	)
	{
		if (trim($name) === '')
		{
			throw new InvalidUserException('User name must not be empty.');
		}

		if (trim($email) === '')
		{
			throw new InvalidUserException('User email must not be empty.');
		}
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getEmail(): string
	{
		return $this->email;
	}
}
