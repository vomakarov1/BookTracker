<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Import;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Application\Port\FileReaderInterface;
use BookTracker\Application\Port\IdGeneratorInterface;
use BookTracker\Application\Port\ImportParserInterface;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use RuntimeException;

final class ImportBooksHandler
{
	/**
	 * @param array<string, ImportParserInterface> $parsers
	 */
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
		private readonly array $parsers,
		private readonly IdGeneratorInterface $idGenerator,
		private readonly FileReaderInterface $fileReader,
	)
	{
	}

	public function handle(ImportBooksCommand $command): int
	{
		try
		{
			$content = $this->fileReader->read($command->filePath);
		}
		catch (RuntimeException $e)
		{
			throw new ImportFailedException(
				sprintf('Failed to read file: %s', $command->filePath),
				previous: $e,
			);
		}

		$parser = $this->parsers[$command->format->value];
		$bookDTOs = $parser->parseBooks($content);

		$imported = 0;

		foreach ($bookDTOs as $dto)
		{
			if ($this->bookRepository->existsByTitle($dto->title))
			{
				continue;
			}

			$id = $this->idGenerator->generate();
			$book = new Book(
				id: $id,
				title: $dto->title,
				author: $dto->author,
				category: $dto->category,
				complexity: $dto->complexity,
			);

			$this->bookRepository->save($book);
			$imported++;
		}

		return $imported;
	}
}
