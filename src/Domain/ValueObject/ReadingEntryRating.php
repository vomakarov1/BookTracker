<?php

declare(strict_types=1);

namespace BookTracker\Domain\ValueObject;

use BookTracker\Domain\Exception\InvalidRatingException;

final class ReadingEntryRating
{
	private int $value;

	public function __construct(int $value)
	{
		if ($value < 1 || $value > 10)
		{
			throw new InvalidRatingException(
				sprintf('Rating must be between 1 and 10, %d given.', $value)
			);
		}

		$this->value = $value;
	}

	public function getValue(): int
	{
		return $this->value;
	}
}
