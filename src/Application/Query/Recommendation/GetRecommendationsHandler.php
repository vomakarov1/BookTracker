<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Recommendation;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\RecommendationDTO;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;
use BookTracker\Domain\Service\RecommendationResult;
use BookTracker\Domain\Service\RecommendationService;

final class GetRecommendationsHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
		private readonly BookRepositoryInterface $bookRepository,
		private readonly RecommendationService $recommendationService,
	)
	{
	}

	/** @return array<RecommendationDTO> */
	public function handle(GetRecommendationsQuery $query): array
	{
		$readingHistory = $this->readingEntryRepository->getByUserId($query->userId);

		$readBooks = array_values(
			array_map(
				fn($entry) => $this->bookRepository->getById($entry->getBookId()),
				$readingHistory,
			)
		);

		$candidates = $this->bookRepository->getAll();

		$results = $this->recommendationService->recommend(
			readingHistory: $readingHistory,
			readBooks: $readBooks,
			candidateBooks: $candidates,
			limit: $query->limit,
		);

		return array_map(
			fn(RecommendationResult $r) => new RecommendationDTO(
				book: $this->toBookDTO($r->book),
				score: $r->score,
				reason: $r->reason,
			),
			$results,
		);
	}

	private function toBookDTO(Book $book): BookDTO
	{
		return new BookDTO(
			id: $book->getId(),
			title: $book->getTitle(),
			author: $book->getAuthor(),
			category: $book->getCategory(),
			complexity: $book->getComplexity(),
		);
	}
}
