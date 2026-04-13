<?php

declare(strict_types=1);

namespace BookTracker\Tests\Infrastructure\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Infrastructure\Export\JsonFormatter;
use BookTracker\Infrastructure\Serializer\AppSerializerFactory;
use JsonException;
use PHPUnit\Framework\TestCase;

final class JsonFormatterTest extends TestCase
{
	/**
	 * @throws JsonException
	 */
	public function testFormatBooksReturnsValidJson(): void
	{
		$formatter = new JsonFormatter(AppSerializerFactory::create());

		$books = [
			new BookDTO('1', 'Clean Code', 'Robert Martin', 'Programming', 5),
			new BookDTO('2', 'The Pragmatic Programmer', 'David Thomas', 'Programming', 4),
			new BookDTO('3', 'Domain-Driven Design', 'Eric Evans', 'Architecture', 8),
		];

		$result = $formatter->formatBooks($books);
		$decoded = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

		$this->assertIsArray($decoded);
		$this->assertCount(3, $decoded);
		$this->assertSame('Clean Code', $decoded[0]['title']);
		$this->assertSame('Domain-Driven Design', $decoded[2]['title']);
	}
}
