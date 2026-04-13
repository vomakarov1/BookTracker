<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Entity;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\InvalidBookException;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
	public function testCreateWithValidData(): void
	{
		$book = new Book('1', 'Clean Code', 'Robert C. Martin', 'Programming', 7);

		$this->assertSame('1', $book->getId());
		$this->assertSame('Clean Code', $book->getTitle());
		$this->assertSame('Robert C. Martin', $book->getAuthor());
		$this->assertSame('Programming', $book->getCategory());
		$this->assertSame(7, $book->getComplexity());
	}

	public function testCreateWithEmptyTitleThrows(): void
	{
		$this->expectException(InvalidBookException::class);
		new Book('1', '', 'Robert C. Martin', 'Programming', 7);
	}

	public function testCreateWithEmptyAuthorThrows(): void
	{
		$this->expectException(InvalidBookException::class);
		new Book('1', 'Clean Code', '', 'Programming', 7);
	}

	public function testCreateWithComplexityZeroThrows(): void
	{
		$this->expectException(InvalidBookException::class);
		new Book('1', 'Clean Code', 'Robert C. Martin', 'Programming', 0);
	}

	public function testCreateWithComplexityElevenThrows(): void
	{
		$this->expectException(InvalidBookException::class);
		new Book('1', 'Clean Code', 'Robert C. Martin', 'Programming', 11);
	}
}
