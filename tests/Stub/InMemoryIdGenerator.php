<?php

declare(strict_types=1);

namespace BookTracker\Tests\Stub;

use BookTracker\Application\Port\IdGeneratorInterface;

final class InMemoryIdGenerator implements IdGeneratorInterface
{
	private int $counter = 1;

	public function generate(): string
	{
		return (string)$this->counter++;
	}
}
