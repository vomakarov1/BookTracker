<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Import;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Application\Port\ImportParserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final class CsvParser implements ImportParserInterface
{
	public function __construct(private readonly SerializerInterface $serializer)
	{
	}

	/**
	 * @return array<BookDTO>
	 */
	public function parseBooks(string $content): array
	{
		if (trim($content) === '')
		{
			return [];
		}

		try
		{
			/** @var array<BookDTO> */
			return $this->serializer->deserialize($content, BookDTO::class . '[]', 'csv');
		}
		catch (Throwable $e)
		{
			throw new ImportFailedException(
				sprintf('Invalid CSV: %s', $e->getMessage()),
				previous: $e,
			);
		}
	}
}
