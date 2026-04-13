<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Import;

use BookTracker\Application\Command\Import\ImportBooksCommand;
use BookTracker\Application\Command\Import\ImportBooksHandler;
use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Application\Port\ImportParserInterface;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use BookTracker\Tests\Stub\InMemoryFileReader;
use BookTracker\Tests\Stub\InMemoryIdGenerator;
use PHPUnit\Framework\TestCase;

final class ImportBooksHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;
	private InMemoryFileReader $fileReader;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
		$this->fileReader = new InMemoryFileReader();
	}

	private function makeHandler(ImportParserInterface $parser): ImportBooksHandler
	{
		return new ImportBooksHandler(
			$this->repository,
			['json' => $parser, 'csv' => $parser],
			new InMemoryIdGenerator(),
			$this->fileReader,
		);
	}

	public function testImportsThreeBooks(): void
	{
		$parser = $this->createStub(ImportParserInterface::class);
		$parser->method('parseBooks')->willReturn(
			[
				new BookDTO('1', 'Clean Code', 'Robert Martin', 'Programming', 5),
				new BookDTO('2', 'The Pragmatic Programmer', 'David Thomas', 'Programming', 4),
				new BookDTO('3', 'Domain-Driven Design', 'Eric Evans', 'Architecture', 8),
			],
		);

		$this->fileReader->addFile('/books.json', '[]');

		$command = new ImportBooksCommand('/books.json', 'json');
		$this->makeHandler($parser)->handle($command);

		$this->assertCount(3, $this->repository->getAll());
	}

	public function testSkipsDuplicateOnImport(): void
	{
		$parser = $this->createStub(ImportParserInterface::class);
		$parser->method('parseBooks')->willReturn(
			[
				new BookDTO('1', 'Clean Code', 'Robert Martin', 'Programming', 5),
				new BookDTO('2', 'Clean Code', 'Robert Martin', 'Programming', 5),
				new BookDTO('3', 'Domain-Driven Design', 'Eric Evans', 'Architecture', 8),
			],
		);

		$this->fileReader->addFile('/books.json', '[]');

		$command = new ImportBooksCommand('/books.json', 'json');
		$this->makeHandler($parser)->handle($command);

		$this->assertCount(2, $this->repository->getAll());
	}

	public function testThrowsOnUnreadableFile(): void
	{
		$this->expectException(ImportFailedException::class);

		$parser = $this->createStub(ImportParserInterface::class);
		// File not added to reader — read() will throw RuntimeException
		$command = new ImportBooksCommand('/non/existent/path/file.json', 'json');

		$this->makeHandler($parser)->handle($command);
	}
}

