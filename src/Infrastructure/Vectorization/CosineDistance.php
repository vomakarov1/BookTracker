<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Vectorization;

use BookTracker\Domain\Service\DistanceMetricInterface;
use BookTracker\Domain\ValueObject\BookVector;

final class CosineDistance implements DistanceMetricInterface
{
	public function distance(BookVector $a, BookVector $b): float
	{
		$dot = 0.0;
		$normA = 0.0;
		$normB = 0.0;

		foreach ($a->toArray() as $i => $valA)
		{
			$valB = $b->toArray()[$i] ?? 0.0;
			$dot += $valA * $valB;
			$normA += $valA * $valA;
			$normB += $valB * $valB;
		}

		$normA = sqrt($normA);
		$normB = sqrt($normB);

		if ($normA === 0.0 || $normB === 0.0)
		{
			return 1.0;
		}

		return 1.0 - ($dot / ($normA * $normB));
	}
}
