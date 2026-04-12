<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

use RuntimeException;

interface FileReaderInterface
{
	/**
	 * @throws RuntimeException When the file cannot be read
	 */
	public function read(string $path): string;
}
