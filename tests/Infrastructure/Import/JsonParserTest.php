<?php

declare(strict_types=1);

namespace BookTracker\Tests\Infrastructure\Import;

use BookTracker\Application\Exception\ImportFailedException;
use BookTracker\Infrastructure\Import\JsonParser;
use BookTracker\Infrastructure\Serializer\AppSerializerFactory;
use PHPUnit\Framework\TestCase;

final class JsonParserTest extends TestCase
{
	private JsonParser $parser;

	protected function setUp(): void
	{
		$this->parser = new JsonParser(AppSerializerFactory::create());
	}

	public function testParseBooksReturnsCorrectDTOs(): void
	{
		$content = (string)file_get_contents(__DIR__ . '/../../Fixture/books.json');
		$books = $this->parser->parseBooks($content);

		$this->assertCount(3, $books);
		$this->assertSame('Clean Code', $books[0]->title);
		$this->assertSame('Robert Martin', $books[0]->author);
		$this->assertSame('Programming', $books[0]->category);
		$this->assertSame(5, $books[0]->complexity);
		$this->assertSame('The Pragmatic Programmer', $books[1]->title);
		$this->assertSame('Domain-Driven Design', $books[2]->title);
		$this->assertSame('Architecture', $books[2]->category);
		$this->assertSame(8, $books[2]->complexity);
	}

	public function testParseBooksThrowsOnInvalidJson(): void
	{
		$this->expectException(ImportFailedException::class);

		$content = (string)file_get_contents(__DIR__ . '/../../Fixture/invalid.json');
		$this->parser->parseBooks($content);
	}
}
