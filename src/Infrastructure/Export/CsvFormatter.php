<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Port\ExportFormatterInterface;

final class CsvFormatter implements ExportFormatterInterface
{
	/**
	 * @param array<BookDTO> $books
	 */
	public function formatBooks(array $books): string
	{
		$lines = [$this->encodeRow(['id', 'title', 'author', 'category', 'complexity'])];

		foreach ($books as $book)
		{
			$lines[] = $this->encodeRow([
				$book->id,
				$book->title,
				$book->author,
				$book->category,
				(string)$book->complexity,
			]);
		}

		return implode("\n", $lines);
	}

	/** @param array<string> $fields */
	private function encodeRow(array $fields): string
	{
		$handle = fopen('php://memory', 'wb');

		assert($handle !== false);
		fputcsv($handle, $fields, escape: '');
		rewind($handle);

		$row = stream_get_contents($handle);
		fclose($handle);

		return rtrim((string)$row, "\r\n");
	}
}
