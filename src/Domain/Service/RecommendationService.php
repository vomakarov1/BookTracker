<?php

declare(strict_types=1);

namespace BookTracker\Domain\Service;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Entity\ReadingEntry;

final class RecommendationService
{
	public function __construct(
		private readonly VectorizerInterface $vectorizer,
		private readonly DistanceMetricInterface $distanceMetric,
	)
	{
	}

	/**
	 * @param array<ReadingEntry> $readingHistory
	 * @param array<Book> $readBooks
	 * @param array<Book> $candidateBooks
	 * @return array<RecommendationResult>
	 */
	public function recommend(
		array $readingHistory,
		array $readBooks,
		array $candidateBooks,
		int $limit = 5,
	): array
	{
		if ($readingHistory === [])
		{
			return [];
		}

		$readBooksById = [];
		foreach ($readBooks as $book)
		{
			$readBooksById[$book->getId()] = $book;
		}

		$readBookIds = [];
		foreach ($readingHistory as $entry)
		{
			$readBookIds[$entry->getBookId()] = true;
		}

		$authorLowRatingCount = [];
		foreach ($readingHistory as $entry)
		{
			$rating = $entry->getRating();
			if ($rating !== null && $rating->getValue() < 3)
			{
				$book = $readBooksById[$entry->getBookId()] ?? null;
				if ($book !== null)
				{
					$author = $book->getAuthor();
					$authorLowRatingCount[$author] = ($authorLowRatingCount[$author] ?? 0) + 1;
				}
			}
		}

		$bannedAuthors = [];
		foreach ($authorLowRatingCount as $author => $count)
		{
			if ($count >= 3)
			{
				$bannedAuthors[$author] = true;
			}
		}

		$filteredCandidates = [];
		foreach ($candidateBooks as $book)
		{
			if (isset($readBookIds[$book->getId()]))
			{
				continue;
			}

			if (isset($bannedAuthors[$book->getAuthor()]))
			{
				continue;
			}

			$filteredCandidates[] = $book;
		}

		if ($filteredCandidates === [])
		{
			return [];
		}

		$referenceBooks = [];
		foreach ($readingHistory as $entry)
		{
			$rating = $entry->getRating();

			if ($rating !== null && $rating->getValue() >= 7)
			{
				$book = $readBooksById[$entry->getBookId()] ?? null;

				if ($book !== null)
				{
					$referenceBooks[] = $book;
				}
			}
		}

		if ($referenceBooks === [])
		{
			$referenceBooks = array_values($readBooksById);
		}

		if ($referenceBooks === [])
		{
			return [];
		}

		/** @var array<array{book: Book, vector: array<float>}> $referenceData */
		$referenceData = [];
		foreach ($referenceBooks as $book)
		{
			$referenceData[] = ['book' => $book, 'vector' => $this->vectorizer->vectorize($book)];
		}

		$results = [];
		foreach ($filteredCandidates as $candidate)
		{
			$candidateVector = $this->vectorizer->vectorize($candidate);

			$minDistance = PHP_FLOAT_MAX;
			$closestBook = $referenceBooks[0];
			$totalDistance = 0.0;

			foreach ($referenceData as $ref)
			{
				$dist = $this->distanceMetric->distance($candidateVector, $ref['vector']);
				$totalDistance += $dist;
				if ($dist < $minDistance)
				{
					$minDistance = $dist;
					$closestBook = $ref['book'];
				}
			}

			$avgDistance = $totalDistance / count($referenceData);
			$score = 1.0 / (1.0 + $avgDistance);
			$reason = sprintf('Похожа на %s', $closestBook->getTitle());

			$results[] = new RecommendationResult($candidate, $score, $reason);
		}

		usort($results, static fn(RecommendationResult $a, RecommendationResult $b): int => $b->score <=> $a->score);

		return array_slice($results, 0, $limit);
	}
}
