<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ExportFailedException;
use BookTracker\Application\Port\ExportFormatterInterface;
use BookTracker\Application\Port\FileWriterInterface;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use RuntimeException;

final class ExportBooksHandler
{
	/**
	 * @param array<string, ExportFormatterInterface> $formatters
	 */
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
		private readonly array $formatters,
		private readonly FileWriterInterface $fileWriter,
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
			$books,
		);

		$formatter = $this->formatters[$command->format->value];
		$content = $formatter->formatBooks($dtos);

		try
		{
			$this->fileWriter->write($command->filePath, $content);
		}
		catch (RuntimeException $e)
		{
			throw new ExportFailedException(
				sprintf('Failed to write file: %s', $command->filePath),
				previous: $e,
			);
		}
	}
}
