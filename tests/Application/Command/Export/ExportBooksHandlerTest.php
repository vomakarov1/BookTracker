<?php

declare(strict_types=1);

namespace BookTracker\Tests\Application\Command\Export;

use BookTracker\Application\Command\Export\ExportBooksCommand;
use BookTracker\Application\Command\Export\ExportBooksHandler;
use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\Exception\ExportFailedException;
use BookTracker\Application\Port\ExportFormatterInterface;
use BookTracker\Application\Port\FileWriterInterface;
use BookTracker\Domain\Entity\Book;
use BookTracker\Tests\Stub\InMemoryBookRepository;
use BookTracker\Tests\Stub\InMemoryFileWriter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExportBooksHandlerTest extends TestCase
{
	private InMemoryBookRepository $repository;
	private InMemoryFileWriter $fileWriter;

	protected function setUp(): void
	{
		$this->repository = new InMemoryBookRepository();
		$this->fileWriter = new InMemoryFileWriter();

		$this->repository->save(new Book('1', 'Clean Code', 'Robert Martin', 'Programming', 5));
		$this->repository->save(new Book('2', 'The Pragmatic Programmer', 'David Thomas', 'Programming', 4));
		$this->repository->save(new Book('3', 'Domain-Driven Design', 'Eric Evans', 'Architecture', 8));
	}

	private function makeHandler(ExportFormatterInterface $formatter): ExportBooksHandler
	{
		return new ExportBooksHandler(
			$this->repository,
			['json' => $formatter, 'csv' => $formatter],
			$this->fileWriter,
		);
	}

	public function testWritesFormattedContentToPath(): void
	{
		$expectedContent = 'formatted_books_content';

		$formatter = $this->createStub(ExportFormatterInterface::class);
		$formatter->method('formatBooks')->willReturn($expectedContent);

		$command = new ExportBooksCommand('/output/books.json', 'json');
		$this->makeHandler($formatter)->handle($command);

		$this->assertTrue($this->fileWriter->hasFile('/output/books.json'));
		$this->assertSame($expectedContent, $this->fileWriter->getContent('/output/books.json'));
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
				},
			)
		;

		$command = new ExportBooksCommand('/output/books.json', 'json');
		$this->makeHandler($formatter)->handle($command);

		$this->assertCount(3, $captured);
	}

	public function testThrowsOnWriteFailure(): void
	{
		$this->expectException(ExportFailedException::class);

		$failingWriter = new class implements FileWriterInterface
		{
			public function write(string $path, string $content): void
			{
				throw new RuntimeException('Disk full');
			}
		};

		$formatter = $this->createStub(ExportFormatterInterface::class);
		$formatter->method('formatBooks')->willReturn('content');

		$handler = new ExportBooksHandler(
			$this->repository,
			['json' => $formatter],
			$failingWriter,
		);

		$handler->handle(new ExportBooksCommand('/output/books.json', 'json'));
	}
}
