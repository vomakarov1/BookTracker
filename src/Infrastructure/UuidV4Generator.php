<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure;

use BookTracker\Application\Port\IdGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class UuidV4Generator implements IdGeneratorInterface
{
	public function generate(): string
	{
		return Uuid::v4()->toRfc4122();
	}
}
