<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Import;

use BookTracker\Application\Command\Import\ImportBooksCommand;
use BookTracker\Application\Command\Import\ImportBooksHandler;
use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Application\Port\ImportParserInterface;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use BookTracker\Tests\Stub\InMemoryIdGenerator;
use PHPUnit\Framework\TestCase;

final class ImportBooksHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
	}

	private function makeHandler(ImportParserInterface $parser): ImportBooksHandler
	{
		return new ImportBooksHandler(
			$this->repository,
			['json' => $parser, 'csv' => $parser],
			new InMemoryIdGenerator(),
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

		$tmpFile = tempnam(sys_get_temp_dir(), 'import_') . '.json';
		file_put_contents($tmpFile, '[]');

		$command = new ImportBooksCommand($tmpFile, 'json');
		$imported = $this->makeHandler($parser)->handle($command);

		unlink($tmpFile);

		$this->assertSame(3, $imported);
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

		$tmpFile = tempnam(sys_get_temp_dir(), 'import_') . '.json';
		file_put_contents($tmpFile, '[]');

		$command = new ImportBooksCommand($tmpFile, 'json');
		$imported = $this->makeHandler($parser)->handle($command);

		unlink($tmpFile);

		$this->assertSame(2, $imported);
		$this->assertCount(2, $this->repository->getAll());
	}

	public function testThrowsOnNonExistentFile(): void
	{
		$this->expectException(ImportFailedException::class);

		$parser = $this->createStub(ImportParserInterface::class);
		$command = new ImportBooksCommand('/non/existent/path/file.json', 'json');

		$this->makeHandler($parser)->handle($command);
	}
}
