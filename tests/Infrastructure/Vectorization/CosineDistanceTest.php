<?php

declare(strict_types=1);

namespace BookTracker\Tests\Infrastructure\Vectorization;

use BookTracker\Domain\ValueObject\BookVector;
use BookTracker\Infrastructure\Vectorization\CosineDistance;
use PHPUnit\Framework\TestCase;

final class CosineDistanceTest extends TestCase
{
	private CosineDistance $distance;

	protected function setUp(): void
	{
		$this->distance = new CosineDistance();
	}

	public function testIdenticalVectorsReturnZero(): void
	{
		$v = new BookVector([1.0, 2.0, 3.0]);

		$this->assertEqualsWithDelta(0.0, $this->distance->distance($v, $v), 1e-9);
	}

	public function testOrthogonalVectorsReturnOne(): void
	{
		$a = new BookVector([1.0, 0.0, 0.0]);
		$b = new BookVector([0.0, 1.0, 0.0]);

		$this->assertEqualsWithDelta(1.0, $this->distance->distance($a, $b), 1e-9);
	}

	public function testZeroVectorReturnsOne(): void
	{
		$zero = new BookVector([0.0, 0.0, 0.0]);
		$v = new BookVector([1.0, 2.0, 3.0]);

		$this->assertSame(1.0, $this->distance->distance($zero, $v));
		$this->assertSame(1.0, $this->distance->distance($v, $zero));
		$this->assertSame(1.0, $this->distance->distance($zero, $zero));
	}

	public function testCollinearVectorsReturnZero(): void
	{
		$a = new BookVector([1.0, 2.0, 3.0]);
		$b = new BookVector([2.0, 4.0, 6.0]);

		$this->assertEqualsWithDelta(0.0, $this->distance->distance($a, $b), 1e-9);
	}

	public function testSymmetry(): void
	{
		$a = new BookVector([1.0, 0.5, 0.3]);
		$b = new BookVector([0.2, 0.8, 1.0]);

		$this->assertEqualsWithDelta(
			$this->distance->distance($a, $b),
			$this->distance->distance($b, $a),
			1e-15,
		);
	}
}
