<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Export;

use BookTracker\Application\Enum\BookFileFormat;
use BookTracker\Application\Exception\ValidationException;
use ValueError;

final readonly class ExportBooksCommand
{
	public string $filePath;
	public BookFileFormat $format;

	public function __construct(string $filePath, string $format)
	{
		if (trim($filePath) === '')
		{
			throw new ValidationException('File path must not be empty.');
		}

		try
		{
			$this->format = BookFileFormat::from($format);
		}
		catch (ValueError)
		{
			throw new ValidationException(
				sprintf(
					'Format must be one of: %s.',
					implode(', ', array_column(BookFileFormat::cases(), 'value')),
				),
			);
		}

		$this->filePath = $filePath;
	}
}
