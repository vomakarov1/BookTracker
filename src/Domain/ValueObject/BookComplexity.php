<?php

declare(strict_types=1);

namespace BookTracker\Domain\ValueObject;

use BookTracker\Domain\Exception\InvalidBookException;

final readonly class BookComplexity
{
	public function __construct(private int $value)
	{
		if ($value < 1 || $value > 10)
		{
			throw new InvalidBookException(
				sprintf('Book complexity must be between 1 and 10, %d given.', $value),
			);
		}
	}

	public function getValue(): int
	{
		return $this->value;
	}
}
