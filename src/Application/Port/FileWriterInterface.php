<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

use RuntimeException;

interface FileWriterInterface
{
	/**
	 * @throws RuntimeException When the file cannot be written
	 */
	public function write(string $path, string $content): void;
}
