<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Export;

use BookTracker\Application\Command\Export\ExportBooksCommand;
use BookTracker\Application\Command\Export\ExportBooksHandler;
use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Port\ExportFormatterInterface;
use BookTracker\Domain\Entity\Book;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use PHPUnit\Framework\TestCase;

final class ExportBooksHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();

		$this->repository->save(new Book('1', 'Clean Code', 'Robert Martin', 'Programming', 5));
		$this->repository->save(new Book('2', 'The Pragmatic Programmer', 'David Thomas', 'Programming', 4));
		$this->repository->save(new Book('3', 'Domain-Driven Design', 'Eric Evans', 'Architecture', 8));
	}

	private function makeHandler(ExportFormatterInterface $formatter): ExportBooksHandler
	{
		return new ExportBooksHandler($this->repository, ['json' => $formatter, 'csv' => $formatter]);
	}

	public function testExportsBooksToFile(): void
	{
		$expectedContent = 'formatted_books_content';

		$formatter = $this->createStub(ExportFormatterInterface::class);
		$formatter->method('formatBooks')->willReturn($expectedContent);

		$tmpFile = tempnam(sys_get_temp_dir(), 'export_') . '.json';

		$command = new ExportBooksCommand($tmpFile, 'json');
		$this->makeHandler($formatter)->handle($command);

		$this->assertFileExists($tmpFile);
		$this->assertSame($expectedContent, file_get_contents($tmpFile));

		unlink($tmpFile);
	}

	public function testFormatterReceivesAllThreeBooks(): void
	{
		/** @var array<BookDTO> $captured */
		$captured = [];

		$formatter = $this->createMock(ExportFormatterInterface::class);
		$formatter
			->expects($this->once())
			->method('formatBooks')
			->willReturnCallback(
				function (array $books) use (&$captured): string
				{
					$captured = $books;

					return 'content';
				}
			)
		;

		$tmpFile = tempnam(sys_get_temp_dir(), 'export_');

		$command = new ExportBooksCommand($tmpFile, 'json');
		$this->makeHandler($formatter)->handle($command);

		unlink($tmpFile);

		$this->assertCount(3, $captured);
	}
}
