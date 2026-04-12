<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\ValueObject;

use BookTracker\Domain\Exception\InvalidRatingException;
use BookTracker\Domain\ValueObject\ReadingEntryRating;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReadingEntryRatingTest extends TestCase
{
	/**
	 * @return array<string, array{int}>
	 */
	public static function validRatingsProvider(): array
	{
		return [
			'minimum (1)' => [1],
			'middle (5)' => [5],
			'maximum (10)' => [10],
		];
	}

	/**
	 * @return array<string, array{int}>
	 */
	public static function invalidRatingsProvider(): array
	{
		return [
			'zero (0)' => [0],
			'above max (11)' => [11],
			'negative (-1)' => [-1],
		];
	}

	#[DataProvider('validRatingsProvider')]
	public function testValidRatingReturnsValue(int $value): void
	{
		$rating = new ReadingEntryRating($value);
		$this->assertSame($value, $rating->getValue());
	}

	#[DataProvider('invalidRatingsProvider')]
	public function testInvalidRatingThrowsException(int $value): void
	{
		$this->expectException(InvalidRatingException::class);
		new ReadingEntryRating($value);
	}
}
