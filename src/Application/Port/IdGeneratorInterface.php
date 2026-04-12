<?php

declare(strict_types=1);

namespace BookTracker\Application\Port;

interface IdGeneratorInterface
{
	public function generate(): string;
}
