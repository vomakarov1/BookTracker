<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

use BookTracker\Application\DTO\BookDTO;

interface ImportParserInterface
{
	/**
	 * @return array<BookDTO>
	 */
	public function parseBooks(string $content): array;
}
