<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Service\Stub;

use BookTracker\Domain\Service\DistanceMetricInterface;
use BookTracker\Domain\ValueObject\BookVector;

final class StubDistanceMetric implements DistanceMetricInterface
{
	public function distance(BookVector $a, BookVector $b): float
	{
		$sum = 0.0;

		foreach ($a->toArray() as $i => $val)
		{
			$diff = $val - ($b->toArray()[$i] ?? 0.0);

			$sum += $diff * $diff;
		}

		return sqrt($sum);
	}
}
