<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Storage;

use JsonException;
use RuntimeException;

final class JsonFileStorage
{
	public function __construct(private readonly string $filePath)
	{
		$dir = dirname($filePath);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir))
		{
			throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
		}

		if (!file_exists($filePath))
		{
			file_put_contents($filePath, '[]');
		}
	}

	/**
	 * @return array<int, array<string, mixed>>
	 * @throws JsonException
	 */
	public function load(): array
	{
		$content = file_get_contents($this->filePath);

		if ($content === false)
		{
			throw new RuntimeException(sprintf('Failed to read storage file: %s', $this->filePath));
		}

		$decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

		if (!is_array($decoded))
		{
			throw new RuntimeException(sprintf('Storage file contains invalid data: %s', $this->filePath));
		}

		/** @var array<int, array<string, mixed>> $decoded */
		return $decoded;
	}

	/**
	 * @param array<int, array<string, mixed>> $data
	 * @throws JsonException
	 */
	public function write(array $data): void
	{
		file_put_contents(
			$this->filePath,
			json_encode(array_values($data), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		);
	}
}
