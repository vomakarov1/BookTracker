<?php

declare(strict_types=1);

namespace BookTracker\Tests\Stub;

use BookTracker\Application\Port\FileReaderInterface;
use RuntimeException;

final class InMemoryFileReader implements FileReaderInterface
{
	/** @var array<string, string> */
	private array $files = [];

	public function addFile(string $path, string $content): void
	{
		$this->files[$path] = $content;
	}

	public function read(string $path): string
	{
		if (!array_key_exists($path, $this->files))
		{
			throw new RuntimeException(sprintf('File not found: %s', $path));
		}

		return $this->files[$path];
	}
}
