<?php

declare(strict_types=1);

namespace BookTracker\Infrastructure\Export;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\UserDTO;
use BookTracker\Application\Port\ExportFormatterInterface;

final class CsvFormatter implements ExportFormatterInterface
{
	/**
	 * @param array<BookDTO> $books
	 */
	public function formatBooks(array $books): string
	{
		$lines = ['id,title,author,category,complexity'];

		foreach ($books as $book)
		{
			$lines[] = implode(
				',',
				[
					$book->id,
					$book->title,
					$book->author,
					$book->category,
					(string)$book->complexity,
				]
			);
		}

		return implode("\n", $lines);
	}

	/**
	 * @param array<UserDTO> $users
	 */
	public function formatUsers(array $users): string
	{
		$lines = ['id,name,email'];

		foreach ($users as $user)
		{
			$lines[] = implode(
				',',
				[
					$user->id,
					$user->name,
					$user->email,
				]
			);
		}

		return implode("\n", $lines);
	}
}
