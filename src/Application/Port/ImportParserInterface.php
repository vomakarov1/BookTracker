<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\UserDTO;

interface ImportParserInterface
{
	/**
	 * @return array<BookDTO>
	 */
	public function parseBooks(string $content): array;

	/**
	 * @return array<UserDTO>
	 */
	public function parseUsers(string $content): array;
}
