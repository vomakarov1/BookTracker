<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\UserDTO;

interface ExportFormatterInterface
{
	/**
	 * @param array<BookDTO> $books
	 */
	public function formatBooks(array $books): string;

	/**
	 * @param array<UserDTO> $users
	 */
	public function formatUsers(array $users): string;
}
