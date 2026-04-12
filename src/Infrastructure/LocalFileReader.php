<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure;

use BookTracker\Application\Port\FileReaderInterface;
use RuntimeException;

final class LocalFileReader implements FileReaderInterface
{
	public function read(string $path): string
	{
		$content = @file_get_contents($path);

		if ($content === false)
		{
			throw new RuntimeException(sprintf('Failed to read file: %s', $path));
		}

		return $content;
	}
}
