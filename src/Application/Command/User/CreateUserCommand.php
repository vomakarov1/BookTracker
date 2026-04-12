<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\User;

use BookTracker\Application\Exception\ValidationException;

final readonly class CreateUserCommand
{
	public string $name;
	public string $email;

	public function __construct(string $name, string $email)
	{
		if (trim($name) === '')
		{
			throw new ValidationException('User name must not be empty.');
		}

		if (trim($email) === '')
		{
			throw new ValidationException('User email must not be empty.');
		}

		$this->name = $name;
		$this->email = $email;
	}
}
