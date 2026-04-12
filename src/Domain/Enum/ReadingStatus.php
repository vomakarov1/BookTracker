<?php

declare(strict_types=1);

namespace BookTracker\Domain\Enum;

enum ReadingStatus: string
{
	case PLANNED = 'planned';
	case READING = 'reading';
	case FINISHED = 'finished';
	case DROPPED = 'dropped';

	public function canTransitionTo(self $nextStatus): bool
	{
		return match ($this)
		{
			self::PLANNED => $nextStatus === self::READING || $nextStatus === self::DROPPED,
			self::READING => $nextStatus === self::FINISHED || $nextStatus === self::DROPPED,
			self::FINISHED => false,
			self::DROPPED => $nextStatus === self::PLANNED,
		};
	}
}
