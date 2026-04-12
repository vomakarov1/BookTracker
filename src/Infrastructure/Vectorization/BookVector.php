<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Vectorization;

final readonly class BookVector
{
	/**
	 * @param array<float> $values
	 */
	public function __construct(
		private array $values,
	)
	{
	}

	/**
	 * @return array<float>
	 */
	public function toArray(): array
	{
		return $this->values;
	}
}
