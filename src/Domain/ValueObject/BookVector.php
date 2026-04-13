<?php

declare(strict_types=1);

namespace BookTracker\Domain\ValueObject;

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

	public function count(): int
	{
		return count($this->values);
	}
}
