<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure;

use BookTracker\Application\Port\FileWriterInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final class LocalFileWriter implements FileWriterInterface
{
	public function __construct(private readonly Filesystem $filesystem)
	{
	}

	public function write(string $path, string $content): void
	{
		try
		{
			$this->filesystem->dumpFile($path, $content);
		}
		catch (IOException $e)
		{
			throw new RuntimeException(sprintf('Failed to write file: %s', $path), previous: $e);
		}
	}
}
