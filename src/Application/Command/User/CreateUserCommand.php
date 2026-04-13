<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\User;

use BookTracker\Application\Exception\ValidationException;

final readonly class CreateUserCommand
{
	public string $id;
	public string $name;
	public string $email;

	public function __construct(string $id, string $name, string $email)
	{
		if (trim($id) === '')
		{
			throw new ValidationException('User id must not be empty.');
		}

		if (trim($name) === '')
		{
			throw new ValidationException('User name must not be empty.');
		}

		if (trim($email) === '')
		{
			throw new ValidationException('User email must not be empty.');
		}

		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
	}
}
