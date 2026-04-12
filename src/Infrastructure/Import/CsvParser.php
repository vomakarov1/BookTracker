<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Import;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Application\Port\ImportParserInterface;

final class CsvParser implements ImportParserInterface
{
	/**
	 * @return array<BookDTO>
	 */
	public function parseBooks(string $content): array
	{
		$rows = $this->parseRows($content);

		if (count($rows) === 0)
		{
			return [];
		}

		$expectedColumns = 5;
		$result = [];

		foreach ($rows as $index => $row)
		{
			if (count($row) !== $expectedColumns)
			{
				throw new ImportFailedException(
					sprintf(
						'Row %d has %d columns, expected %d.',
						$index + 1,
						count($row),
						$expectedColumns,
					),
				);
			}

			$result[] = new BookDTO(
				id: $row[0],
				title: $row[1],
				author: $row[2],
				category: $row[3],
				complexity: (int)$row[4],
			);
		}

		return $result;
	}

	/**
	 * @return array<array<string>>
	 */
	private function parseRows(string $content): array
	{
		$trimmed = trim($content);

		if ($trimmed === '')
		{
			return [];
		}

		$lines = explode("\n", $trimmed);

		// Skip header row
		array_shift($lines);

		$rows = [];

		foreach ($lines as $line)
		{
			$line = trim($line);

			if ($line === '')
			{
				continue;
			}

			$row = str_getcsv($line, ',', '"', '');
			$rows[] = array_map(static fn(string|null $v): string => trim((string)$v), $row);
		}

		return $rows;
	}
}
