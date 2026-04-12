<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\User;

use BookTracker\Application\Exception\ValidationException;

final readonly class DeleteUserCommand
{
	public string $id;

	public function __construct(string $id)
	{
		if (trim($id) === '')
		{
			throw new ValidationException('User id must not be empty.');
		}

		$this->id = $id;
	}
}
