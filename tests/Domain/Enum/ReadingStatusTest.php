<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Enum;

use BookTracker\Domain\Enum\ReadingStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReadingStatusTest extends TestCase
{
	/**
	 * @return array<string, array{ReadingStatus, ReadingStatus}>
	 */
	public static function allowedTransitionsProvider(): array
	{
		return [
			'PLANNED → READING' => [ReadingStatus::PLANNED, ReadingStatus::READING],
			'PLANNED → DROPPED' => [ReadingStatus::PLANNED, ReadingStatus::DROPPED],
			'READING → FINISHED' => [ReadingStatus::READING, ReadingStatus::FINISHED],
			'READING → DROPPED' => [ReadingStatus::READING, ReadingStatus::DROPPED],
			'DROPPED → PLANNED' => [ReadingStatus::DROPPED, ReadingStatus::PLANNED],
		];
	}

	/**
	 * @return array<string, array{ReadingStatus, ReadingStatus}>
	 */
	public static function forbiddenTransitionsProvider(): array
	{
		return [
			'FINISHED → PLANNED' => [ReadingStatus::FINISHED, ReadingStatus::PLANNED],
			'FINISHED → READING' => [ReadingStatus::FINISHED, ReadingStatus::READING],
			'FINISHED → DROPPED' => [ReadingStatus::FINISHED, ReadingStatus::DROPPED],
			'PLANNED → FINISHED' => [ReadingStatus::PLANNED, ReadingStatus::FINISHED],
			'READING → PLANNED' => [ReadingStatus::READING, ReadingStatus::PLANNED],
			'DROPPED → READING' => [ReadingStatus::DROPPED, ReadingStatus::READING],
			'DROPPED → FINISHED' => [ReadingStatus::DROPPED, ReadingStatus::FINISHED],
		];
	}

	/**
	 * @return array<string, array{ReadingStatus}>
	 */
	public static function sameStatusProvider(): array
	{
		return [
			'PLANNED → PLANNED' => [ReadingStatus::PLANNED],
			'READING → READING' => [ReadingStatus::READING],
			'FINISHED → FINISHED' => [ReadingStatus::FINISHED],
			'DROPPED → DROPPED' => [ReadingStatus::DROPPED],
		];
	}

	#[DataProvider('allowedTransitionsProvider')]
	public function testAllowedTransitionReturnsTrue(ReadingStatus $from, ReadingStatus $to): void
	{
		$this->assertTrue($from->canTransitionTo($to));
	}

	#[DataProvider('forbiddenTransitionsProvider')]
	public function testForbiddenTransitionReturnsFalse(ReadingStatus $from, ReadingStatus $to): void
	{
		$this->assertFalse($from->canTransitionTo($to));
	}

	#[DataProvider('sameStatusProvider')]
	public function testSameStatusTransitionReturnsFalse(ReadingStatus $status): void
	{
		$this->assertFalse($status->canTransitionTo($status));
	}
}
