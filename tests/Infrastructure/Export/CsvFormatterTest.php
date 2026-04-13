<?php

declare(strict_types=1);

namespace BookTracker\Tests\Infrastructure\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Infrastructure\Export\CsvFormatter;
use PHPUnit\Framework\TestCase;

final class CsvFormatterTest extends TestCase
{
	public function testFormatBooksQuotesFieldsContainingCommas(): void
	{
		$formatter = new CsvFormatter();

		$books = [
			new BookDTO('1', 'War, and Peace', 'Tolstoy', 'Fiction', 6),
			new BookDTO('2', 'He said "Hello"', 'Author', 'Fiction', 3),
		];

		$result = $formatter->formatBooks($books);
		$lines = explode("\n", $result);

		$this->assertStringContainsString('"War, and Peace"', $lines[1]);
		$this->assertStringContainsString('"He said ""Hello"""', $lines[2]);
	}

	public function testFormatBooksReturnsHeaderAndDataRows(): void
	{
		$formatter = new CsvFormatter();

		$books = [
			new BookDTO('1', 'Clean Code', 'Robert Martin', 'Programming', 5),
			new BookDTO('2', 'The Pragmatic Programmer', 'David Thomas', 'Programming', 4),
			new BookDTO('3', 'Domain-Driven Design', 'Eric Evans', 'Architecture', 8),
		];

		$result = $formatter->formatBooks($books);
		$lines = explode("\n", $result);

		$this->assertSame('id,title,author,category,complexity', $lines[0]);
		$this->assertCount(4, $lines);
		$this->assertStringContainsString('Clean Code', $lines[1]);
		$this->assertStringContainsString('Domain-Driven Design', $lines[3]);
	}
}
