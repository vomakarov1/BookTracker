<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure;

use BookTracker\Application\Port\FileWriterInterface;
use RuntimeException;

final class LocalFileWriter implements FileWriterInterface
{
	public function write(string $path, string $content): void
	{
		$result = @file_put_contents($path, $content);

		if ($result === false)
		{
			throw new RuntimeException(sprintf('Failed to write file: %s', $path));
		}
	}
}
