<?php

declare(strict_types=1);

namespace BookTracker\Application\Enum;

enum BookFileFormat: string
{
	case Json = 'json';
	case Csv = 'csv';
}
