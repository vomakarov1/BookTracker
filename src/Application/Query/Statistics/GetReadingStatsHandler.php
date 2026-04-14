<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Statistics;

use BookTracker\Application\DTO\ReadingStatsDTO;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Enum\ReadingStatus;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;

final class GetReadingStatsHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
		private readonly BookRepositoryInterface $bookRepository,
	)
	{
	}

	public function handle(GetReadingStatsQuery $query): ReadingStatsDTO
	{
		$entries = $this->readingEntryRepository->getByUserId($query->userId);

		$countsByStatus = $this->buildCountsByStatus($entries);
		$averageRatingByAuthor = $this->buildAverageRatingByAuthor($entries);
		$finishedByMonth = $this->buildFinishedByMonth($entries);

		return new ReadingStatsDTO(
			countsByStatus: $countsByStatus,
			averageRatingByAuthor: $averageRatingByAuthor,
			finishedByMonth: $finishedByMonth,
		);
	}

	/**
	 * @param array<ReadingEntry> $entries
	 * @return array<string, int>
	 */
	private function buildCountsByStatus(array $entries): array
	{
		$counts = [];

		foreach (ReadingStatus::cases() as $status)
		{
			$counts[$status->value] = 0;
		}

		foreach ($entries as $entry)
		{
			$counts[$entry->getStatus()->value]++;
		}

		return $counts;
	}

	/**
	 * @param array<ReadingEntry> $entries
	 * @return array<string, float>
	 */
	private function buildAverageRatingByAuthor(array $entries): array
	{
		$bookIds = array_unique(
			array_map(static fn(ReadingEntry $e) => $e->getBookId(), $entries),
		);
		$books = $this->bookRepository->getByIds($bookIds);

		/** @var array<string, array{sum: int, count: int}> $accumulator */
		$accumulator = [];

		foreach ($entries as $entry)
		{
			$rating = $entry->getRating();

			if ($rating === null)
			{
				continue;
			}

			$book = $books[$entry->getBookId()] ?? null;

			if ($book === null)
			{
				continue;
			}

			$author = $book->getAuthor();

			if (!isset($accumulator[$author]))
			{
				$accumulator[$author] = ['sum' => 0, 'count' => 0];
			}

			$accumulator[$author]['sum'] += $rating->getValue();
			$accumulator[$author]['count']++;
		}

		$averages = [];

		foreach ($accumulator as $author => $data)
		{
			$averages[$author] = round($data['sum'] / $data['count'], 1);
		}

		arsort($averages);

		return $averages;
	}

	/**
	 * @param array<ReadingEntry> $entries
	 * @return array<string, int>
	 */
	private function buildFinishedByMonth(array $entries): array
	{
		$byMonth = [];

		foreach ($entries as $entry)
		{
			if ($entry->getStatus() !== ReadingStatus::FINISHED)
			{
				continue;
			}

			$finishedAt = $entry->getFinishedAt();

			if ($finishedAt === null)
			{
				continue;
			}

			$month = $finishedAt->format('Y-m');
			$byMonth[$month] = ($byMonth[$month] ?? 0) + 1;
		}

		ksort($byMonth);

		return $byMonth;
	}
}
