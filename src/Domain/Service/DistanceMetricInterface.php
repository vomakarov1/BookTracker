<?php

declare(strict_types=1);

namespace BookTracker\Domain\Service;

use BookTracker\Domain\ValueObject\BookVector;

interface DistanceMetricInterface
{
	public function distance(BookVector $a, BookVector $b): float;
}
