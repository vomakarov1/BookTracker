<?php

declare(strict_types=1);

namespace BookTracker\Tests\Domain\Service\Stub;

use BookTracker\Domain\Service\DistanceMetricInterface;

final class StubDistanceMetric implements DistanceMetricInterface
{
	/**
	 * @param array<float> $a
	 * @param array<float> $b
	 */
	public function distance(array $a, array $b): float
	{
		$sum = 0.0;
		foreach ($a as $i => $val)
		{
			$diff = $val - ($b[$i] ?? 0.0);
			$sum += $diff * $diff;
		}

		return sqrt($sum);
	}
}
