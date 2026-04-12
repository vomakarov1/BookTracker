<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Port\ExportFormatterInterface;
use JsonException;

final class JsonFormatter implements ExportFormatterInterface
{
	/**
	 * @param array<BookDTO> $books
	 * @throws JsonException
	 */
	public function formatBooks(array $books): string
	{
		$data = array_map(
			static fn(BookDTO $book) => [
				'id' => $book->id,
				'title' => $book->title,
				'author' => $book->author,
				'category' => $book->category,
				'complexity' => $book->complexity,
			],
			$books,
		);

		return (string)json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
}
