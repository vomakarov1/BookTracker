<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Port\ExportFormatterInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class JsonFormatter implements ExportFormatterInterface
{
	public function __construct(private readonly SerializerInterface $serializer)
	{
	}

	/**
	 * @param array<BookDTO> $books
	 * @throws ExceptionInterface
	 */
	public function formatBooks(array $books): string
	{
		return $this->serializer->serialize(
			$books,
			'json',
			[
				JsonEncode::OPTIONS => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
			],
		);
	}
}
