<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Import;

use BookTracker\Application\Exception\ValidationException;

final readonly class ImportBooksCommand
{
	public string $filePath;
	public string $format;

	private const array ALLOWED_FORMATS = ['json', 'csv'];

	public function __construct(string $filePath, string $format)
	{
		if (trim($filePath) === '')
		{
			throw new ValidationException('File path must not be empty.');
		}

		if (!in_array($format, self::ALLOWED_FORMATS, true))
		{
			throw new ValidationException(
				sprintf('Format must be one of: %s.', implode(', ', self::ALLOWED_FORMATS))
			);
		}

		$this->filePath = $filePath;
		$this->format = $format;
	}
}
