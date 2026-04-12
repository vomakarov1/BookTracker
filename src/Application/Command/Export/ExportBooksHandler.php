<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ExportFailedException;
use BookTracker\Application\Port\ExportFormatterInterface;
use BookTracker\Domain\Repository\BookRepositoryInterface;

final class ExportBooksHandler
{
	/**
	 * @param array<string, ExportFormatterInterface> $formatters
	 */
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
		private readonly array $formatters,
	)
	{
	}

	public function handle(ExportBooksCommand $command): void
	{
		$books = $this->bookRepository->getAll();

		$dtos = array_map(
			fn($book) => new BookDTO(
				id: $book->getId(),
				title: $book->getTitle(),
				author: $book->getAuthor(),
				category: $book->getCategory(),
				complexity: $book->getComplexity(),
			),
			$books
		);

		$formatter = $this->formatters[$command->format];
		$content = $formatter->formatBooks($dtos);

		$result = @file_put_contents($command->filePath, $content);

		if ($result === false)
		{
			throw new ExportFailedException(
				sprintf('Failed to write file: %s', $command->filePath)
			);
		}
	}
}
