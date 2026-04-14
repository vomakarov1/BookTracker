<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Storage;

use JsonException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockFactory;

final class JsonFileStorage
{
	public function __construct(
		private readonly string $filePath,
		private readonly Filesystem $filesystem,
		private readonly LockFactory $lockFactory,
	)
	{
		if (!$this->filesystem->exists($filePath))
		{
			$this->filesystem->dumpFile($filePath, '[]');
		}
	}

	/**
	 * @return array<int, array<string, mixed>>
	 * @throws JsonException
	 */
	public function load(): array
	{
		if (!$this->filesystem->exists($this->filePath))
		{
			throw new RuntimeException(sprintf('Storage file not found: %s', $this->filePath));
		}

		$content = (string)file_get_contents($this->filePath);

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
		$lock = $this->lockFactory->createLock('json_storage_' . md5($this->filePath));
		$lock->acquire(blocking: true);

		try
		{
			$this->filesystem->dumpFile(
				$this->filePath,
				(string)json_encode(
					array_values($data),
					JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
				),
			);
		}
		finally
		{
			$lock->release();
		}
	}
}
