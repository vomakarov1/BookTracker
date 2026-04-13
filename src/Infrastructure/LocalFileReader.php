<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure;

use BookTracker\Application\Port\FileReaderInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

final class LocalFileReader implements FileReaderInterface
{
	public function __construct(private readonly Filesystem $filesystem)
	{
	}

	public function read(string $path): string
	{
		if (!$this->filesystem->exists($path))
		{
			throw new RuntimeException(sprintf('File not found: %s', $path));
		}

		return (string)file_get_contents($path);
	}
}
