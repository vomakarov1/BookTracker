<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\ValueObject;

use BookTracker\Domain\Exception\InvalidBookException;
use BookTracker\Domain\ValueObject\BookComplexity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BookComplexityTest extends TestCase
{
	/**
	 * @return array<string, array{int}>
	 */
	public static function validComplexityProvider(): array
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
	public static function invalidComplexityProvider(): array
	{
		return [
			'zero (0)' => [0],
			'above max (11)' => [11],
			'negative (-1)' => [-1],
		];
	}

	#[DataProvider('validComplexityProvider')]
	public function testValidComplexityReturnsValue(int $value): void
	{
		$complexity = new BookComplexity($value);
		$this->assertSame($value, $complexity->getValue());
	}

	#[DataProvider('invalidComplexityProvider')]
	public function testInvalidComplexityThrowsException(int $value): void
	{
		$this->expectException(InvalidBookException::class);
		new BookComplexity($value);
	}
}
