<?php

declare(strict_types=1);

namespace BookTracker\Tests\Stub;

use BookTracker\Application\Port\FileWriterInterface;

final class InMemoryFileWriter implements FileWriterInterface
{
	/** @var array<string, string> */
	private array $files = [];

	public function write(string $path, string $content): void
	{
		$this->files[$path] = $content;
	}

	public function getContent(string $path): string
	{
		return $this->files[$path] ?? '';
	}

	public function hasFile(string $path): bool
	{
		return array_key_exists($path, $this->files);
	}
}
