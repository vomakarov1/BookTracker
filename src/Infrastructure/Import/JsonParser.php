<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Import;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Application\Port\ImportParserInterface;

final class JsonParser implements ImportParserInterface
{
	/**
	 * @return array<BookDTO>
	 */
	public function parseBooks(string $content): array
	{
		$data = $this->decode($content);

		return array_map(
			static fn(array $item) => new BookDTO(
				id: (string)($item['id'] ?? ''),
				title: (string)($item['title'] ?? ''),
				author: (string)($item['author'] ?? ''),
				category: (string)($item['category'] ?? ''),
				complexity: (int)($item['complexity'] ?? 1),
			),
			$data,
		);
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function decode(string $content): array
	{
		$data = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE)
		{
			throw new ImportFailedException(
				sprintf('Invalid JSON: %s', json_last_error_msg()),
			);
		}

		if (!is_array($data))
		{
			throw new ImportFailedException('JSON root must be an array.');
		}

		return $data;
	}
}
