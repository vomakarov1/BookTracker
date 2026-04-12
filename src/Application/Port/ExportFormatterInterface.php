<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

use BookTracker\Application\DTO\BookDTO;

interface ExportFormatterInterface
{
	/**
	 * @param array<BookDTO> $books
	 */
	public function formatBooks(array $books): string;
}
