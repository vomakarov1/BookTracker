<?php

declare(strict_types=1);

namespace BookTracker\Domain\Service;

interface DistanceMetricInterface
{
	/**
	 * @param array<float> $a
	 * @param array<float> $b
	 */
	public function distance(array $a, array $b): float;
}
